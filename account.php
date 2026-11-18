<?php 
// Richiama la sessione esistente o ne crea una nuova se non esiste.
session_start();

if (!isset($_SESSION["idUser"])) {
    // Se l'utente non è loggato, reindirizza alla pagina di login.
    header("Location: login.php");
    exit();
}

$todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
$today = $todayDate->format("Y-m-d");

// Crea il token per le richieste di annullamento, se non esiste.
if (!isset($_SESSION["cancelToken"])) {
    $_SESSION["cancelToken"] = bin2hex(random_bytes(32));
}

// Crea il token per proteggere l'uscita, se non esiste.
if (!isset($_SESSION["logoutToken"])) {
    $_SESSION["logoutToken"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/includes/db.php";
$query = "SELECT bookings.idBookings, rooms.roomNumber,
                 bookings.checkIn, bookings.checkOut, bookings.numGuests,
                 bookings.totalPrice, bookings.status
          FROM bookings
          JOIN rooms ON bookings.idRoom = rooms.idRoom
          WHERE bookings.idUser = ?
          ORDER BY bookings.checkIn DESC";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $_SESSION["idUser"]);
$stmt->execute();
$bookingsResult = $stmt->get_result();



?>

<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Area personale</title>
    <link rel="stylesheet" href="./css/styles.css?v=2" />
  </head>
  <body>
    <?php require_once __DIR__ . "/includes/nav.php"; ?>
    <h1>Area personale</h1>
    <?php if(isset($_SESSION["successMessage"])){

    echo '<p class="success-message">'
    . htmlspecialchars($_SESSION["successMessage"], ENT_QUOTES, "UTF-8")
    . '</p>';
    unset($_SESSION["successMessage"]);

    } 
    if($bookingsResult->num_rows > 0){

      while ($booking = $bookingsResult->fetch_assoc()): ?>
      <article class="booking-card">
      <h2>Codice prenotazione: #<?php echo $booking["idBookings"]; ?></h2>
      <p>Camera: <?php echo $booking["roomNumber"]; ?></p>
      <p>Arrivo: <?php echo $booking["checkIn"]; ?></p>
      <p>Partenza: <?php echo $booking["checkOut"]; ?></p>
      <p>Ospiti: <?php echo $booking["numGuests"]; ?></p>
      <p>Totale: <?php echo number_format($booking["totalPrice"], 2, ",", "."); ?> €</p>
      <?php if ($booking["status"] === 1) { ?>
  <p>Stato: <span class="status-badge status-confirmed">Confermata</span></p>

  <?php if ($booking["checkIn"] > $today) { ?>
    <form action="cancel-booking.php" method="post">
      <input type="hidden" name="idBookings"
             value="<?php echo $booking["idBookings"]; ?>" />
      <input type="hidden" name="cancelToken"
             value="<?php echo $_SESSION["cancelToken"]; ?>" />
      <button type="submit" class="button-danger">
        Annulla prenotazione
      </button>
    </form>
  <?php } ?>

  <?php } else { ?>
  <p>Stato: <span class="status-badge status-cancelled">Annullata</span></p>
  <?php } ?>
      </article>
      <?php endwhile;
      }else{
        echo "<p>Non hai ancora prenotazioni</p>";
      } ?>
    
     
    <form action="logout.php" method="post" class="logout-form">
      <input type ="hidden" name="logoutToken" value="<?php echo $_SESSION["logoutToken"]?>">
        <button type="submit" class="button-secondary">Esci</button>
    </form>
  </body>
</html>
