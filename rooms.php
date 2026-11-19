<?php
require_once __DIR__ . "/includes/db.php";

$query = "SELECT roomNumber, capacity, pricePerNight, description, imagePath FROM rooms ";

// Distingue il catalogo dalla ricerca, che permette anche di scegliere una camera.
$searchPerformed = false;

if (isset($_GET["numGuests"], $_GET["checkIn"], $_GET["checkOut"])) {
    $checkIn = $_GET["checkIn"];
    $checkOut = $_GET["checkOut"];
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

    $todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
    $today = $todayDate->format("Y-m-d");

    if ($checkIn < $today) {
        die("La data di check-in non può essere nel passato.");
    }

    $numGuests = filter_var($_GET["numGuests"], FILTER_VALIDATE_INT);

    // Accetta soltanto numeri interi compresi tra 1 e 3.
    if ($numGuests !== false && $numGuests >= 1 && $numGuests <= 3) {

        // Gli intervalli escludono la partenza, che resta disponibile per nuovi arrivi.
        $query = "SELECT idRoom, roomNumber, capacity, pricePerNight, description, imagePath
              FROM rooms
              WHERE capacity >= ?
                AND idRoom NOT IN (
                    SELECT idRoom
                    FROM bookings
                    WHERE checkIn < ?
                      AND checkOut > ?
                      AND status = 1
                )
              ORDER BY roomNumber ASC";

        $stmt = $connection->prepare($query);

        $stmt->bind_param("iss", $numGuests, $checkOut, $checkIn);

        $stmt->execute();

        $result = $stmt->get_result();
        $searchPerformed = true;
    } else {

        die("Numero di ospiti non valido. Inserire un numero intero tra 1 e 3.");
    }
} else {
    $query .= " ORDER BY roomNumber ASC";
    $result = $connection->query($query);
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Camere | Rifugio Sentiero Alto</title>
    <link rel="stylesheet" href="./css/styles.css?v=2" />
</head>

<body>
    <?php require_once __DIR__ . "/includes/nav.php"; ?>

    <h1>Le nostre camere</h1>

    <form action="rooms.php" method="get" class="search-form">
        <div class="form-field">
            <label for="checkIn">Arrivo:</label>
            <input type="date" name="checkIn" id="checkIn"
                value="<?php echo htmlspecialchars($checkIn ?? "", ENT_QUOTES, "UTF-8"); ?>"
                required />
        </div>
        <div class="form-field">
            <label for="checkOut">Partenza:</label>
            <input type="date" name="checkOut" id="checkOut"
                value="<?php echo htmlspecialchars($checkOut ?? "", ENT_QUOTES, "UTF-8"); ?>"
                required />
        </div>
        <div class="form-field">
            <label for="numGuests">Numero di ospiti:</label>
            <input type="number" name="numGuests" id="numGuests"
                min="1" max="3"
                value="<?php echo htmlspecialchars((string) ($numGuests ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                required />
        </div>
        <button type="submit" class="button-primary">Cerca disponibilità</button>
    </form>

    <?php
    if ($result->num_rows > 0) {
        while ($room = $result->fetch_assoc()) {

            echo '<article class="room">';
            echo '<img src="' . htmlspecialchars((string) $room["imagePath"], ENT_QUOTES, "UTF-8") . '" alt="Interno della camera ' . $room["roomNumber"] . '">';
            echo '<div class="room-info">'; // Il testo resta un unico elemento flex accanto all'immagine.
            echo "<h2>Camera " . $room["roomNumber"] . " </h2>";
            echo "<p>" . $room["capacity"] . " ospiti - " . $room["pricePerNight"] . " euro a notte</p>";
            echo "<p>" . htmlspecialchars((string) $room["description"], ENT_QUOTES, "UTF-8") . "</p>";
            if ($searchPerformed) {
                echo '<form action="booking.php" method="post">';
                echo '<input type="hidden" name="idRoom" value="' . $room["idRoom"] . '">';
                echo '<input type="hidden" name="checkIn" value="' . $checkIn . '">';
                echo '<input type="hidden" name="checkOut" value="' . $checkOut . '">';
                echo '<input type="hidden" name="numGuests" value="' . $numGuests . '">';
                echo '<button type="submit" class="button-primary">Scegli questa camera</button>';
                echo '</form>';
            }
            echo "</div>";
            echo "</article>";
        }
    } else {

        echo "<p>Nessuna camera trovata</p>";
    }
    ?>
</body>

</html>