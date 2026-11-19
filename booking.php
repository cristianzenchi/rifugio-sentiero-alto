<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  if (isset($_POST["idRoom"], $_POST["numGuests"], $_POST["checkIn"], $_POST["checkOut"])) {
    $idRoom = $_POST["idRoom"];
    $numGuests = $_POST["numGuests"];
    $checkIn = $_POST["checkIn"];
    $checkOut = $_POST["checkOut"];

    $idRoom = filter_var($idRoom, FILTER_VALIDATE_INT);
    $numGuests = filter_var($numGuests, FILTER_VALIDATE_INT);

    if ($idRoom === false || $idRoom <= 0 || $numGuests === false || $numGuests < 1 || $numGuests > 3) {
      die("ID camera o numero di ospiti non validi");
    }

    // La regex controlla il formato; checkdate() esclude date inesistenti.
    $checkInValid = is_string($checkIn)
      && preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $checkIn) === 1;
    $checkOutValid = is_string($checkOut)
      && preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $checkOut) === 1;

    if ($checkInValid) {

      [
        $year,
        $month,
        $day
      ] = explode("-", $checkIn);

      $checkInValid = checkdate((int)$month, (int)$day, (int)$year);
    }

    if ($checkOutValid) {

      [
        $year,
        $month,
        $day
      ] = explode("-", $checkOut);

      $checkOutValid = checkdate((int)$month, (int)$day, (int)$year);
    }
    if (!$checkInValid || !$checkOutValid) {

      die("Data di check-in o check-out non valida.");
    } else if ($checkIn >= $checkOut) {

      die("La data di check-in deve essere precedente alla data di check-out.");
    }

    // Conserva la scelta del soggiorno anche durante il passaggio dal login
    $_SESSION["pendingBooking"] = [
      "idRoom" => $idRoom,
      "numGuests" => $numGuests,
      "checkIn" => $checkIn,
      "checkOut" => $checkOut
    ];
  } else {
    die("Dati del soggiorno mancanti. Ripeti la ricerca delle camere.");
  }
}
// Questi controlli valgono anche per GET, quando si torna dal login o dalla cucina.
if (!isset($_SESSION["idUser"])) {
  header("Location: login.php");
  exit();
}

if (!isset($_SESSION["pendingBooking"])) {
  header("Location: rooms.php");
  exit();
}

$pendingBooking = $_SESSION["pendingBooking"];
$idRoom = $pendingBooking["idRoom"];
$numGuests = $pendingBooking["numGuests"];
$checkIn = $pendingBooking["checkIn"];
$checkOut = $pendingBooking["checkOut"];


// Recupera le opzioni salvate; alla prima apertura usa i valori predefiniti.
$breakfast = $pendingBooking["breakfast"] ?? 0;
$dinner = $pendingBooking["dinner"] ?? 0;
$idMenu = $pendingBooking["idMenu"] ?? null;


require_once __DIR__ . "/includes/db.php";
// La disponibilità viene ricontrollata anche quando si riapre il riepilogo.
$query = "SELECT idRoom, roomNumber, capacity, pricePerNight, description, imagePath
              FROM rooms
              WHERE idRoom = ?
                AND capacity >= ?
                AND idRoom NOT IN (
                    SELECT idRoom
                    FROM bookings
                    WHERE checkIn < ?
                      AND checkOut > ?
                      AND status = 1
                )";


$stmt = $connection->prepare($query);

$stmt->bind_param("iiss", $idRoom, $numGuests, $checkOut, $checkIn);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("La camera non è disponibile per il soggiorno selezionato. Ripeti la ricerca.");
}

$room = $result->fetch_assoc();
// Prezzo base senza colazione; il totale definitivo viene ricalcolato sul server.
$checkInDate = new DateTimeImmutable($checkIn);
$checkOutDate = new DateTimeImmutable($checkOut);
$nights = $checkInDate->diff($checkOutDate)->days;
$totalPrice = $nights * $room["pricePerNight"];

$query = "SELECT idMenu, name, pricePerMenu FROM menus ORDER BY idMenu ASC";
$menusResult = $connection->query($query);

// Usa lo stesso fuso orario dei controlli di conferma e annullamento.
$todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
$today = $todayDate->format("Y-m-d");

if ($checkIn < $today) {
  die("La data di check-in non può essere nel passato.");
}

// Il token CSRF resta stabile per la bozza, anche quando si torna dalla cucina.
if (!isset($_SESSION["pendingBooking"]["token"])) {
  $_SESSION["pendingBooking"]["token"] = bin2hex(random_bytes(32));
}


?>

<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prenotazione</title>
  <link rel="stylesheet" href="./css/styles.css?v=2" />
  <script src="./js/booking.js" defer></script>
</head>

<body>
  <?php require_once __DIR__ . "/includes/nav.php"; ?>

  <h1>Conferma prenotazione</h1>
  <h2>Camera <?php echo $room["roomNumber"]; ?></h2>
  <div class="booking-layout">
    <section class="booking-summary">
      <p>Arrivo: <?php echo $checkIn; ?></p>
      <p>Partenza: <?php echo $checkOut; ?></p>
      <p>Numero di ospiti: <?php echo $numGuests; ?></p>
      <p>Numero di notti: <?php echo $nights; ?></p>
      <p id="booking-total" data-base-total="<?php echo $totalPrice ?>"
        data-guests="<?php echo $numGuests ?>" data-nights="<?php echo $nights ?>">
        Totale camera (colazione esclusa):
        <?php echo number_format($totalPrice, 2, ",", "."); ?> €
      </p>
    </section>

    <form action="confirm-booking.php" method="post" class="booking-form">
      <input type="hidden" name="bookingToken"
        value="<?php echo $_SESSION["pendingBooking"]["token"]; ?>" />

      <div class="checkbox-field">
        <input type="checkbox" name="breakfast" value="1" id="breakfast"
          <?php if ($breakfast === 1) {
            echo "checked";
          } ?> />
        <label for="breakfast">
          Aggiungi colazione (8 € per persona a notte)
        </label>
      </div>

      <div class="checkbox-field">
        <input type="checkbox" name="dinner" value="1" id="dinner"
          <?php if ($dinner === 1) {
            echo "checked";
          } ?> />
        <label for="dinner">
          Aggiungi la cena per tutti gli ospiti la sera dell’arrivo
          (pagamento in struttura)
        </label>
      </div>

      <div class="form-field">
        <label for="idMenu">Menu per la cena d’arrivo</label>
        <select name="idMenu" id="idMenu">
          <option value="">Nessun menu fisso</option>
          <?php while ($menu = $menusResult->fetch_assoc()): ?>
            <option value="<?php echo $menu["idMenu"]; ?>"
              <?php if ($idMenu === (int) $menu["idMenu"]) {
                echo "selected";
              } ?>>
              <?php
              echo htmlspecialchars($menu["name"], ENT_QUOTES, "UTF-8")
                . ": " . number_format($menu["pricePerMenu"], 2, ",", ".") . " €";
              ?>
            </option>
          <?php endwhile; ?>
        </select>
        <p>Prezzi per persona. Senza menu fisso, scegli sul posto.</p>
      </div>

      <div class="booking-actions">
        <button type="submit" formaction="save-booking-options.php" class="button-secondary">
          Consulta i menu
        </button>
        <button type="submit" class="button-primary">
          Conferma prenotazione
        </button>
      </div>
    </form>
  </div>
</body>

</html>