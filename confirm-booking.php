<?php

session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST"){

    header("Location:booking.php");
    exit();

    }

    if (!isset($_SESSION["idUser"])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_SESSION["pendingBooking"])) {
        header("Location: rooms.php");
        exit();
    }

    
    // Rifiuta token mancanti o un valore ricevuto che non sia una stringa.
    if (
    !isset($_POST["bookingToken"], $_SESSION["pendingBooking"]["token"]) ||
    !is_string($_POST["bookingToken"])) {
        die("Conferma non valida. Riapri il riepilogo.");
    }

    // Rifiuta il token ricevuto se non corrisponde a quello della bozza.
    if (!hash_equals(
    $_SESSION["pendingBooking"]["token"],
    $_POST["bookingToken"]
    )) {
        die("Conferma non valida. Riapri il riepilogo.");
    }

    // Le checkbox assenti valgono 0; se inviate accettano soltanto la stringa "1".
    $breakfast = 0;
    if(isset($_POST["breakfast"])) {

        if($_POST["breakfast"] === "1") {
         
            $breakfast = 1;
        
        } else {

            die("Opzione non valida.");
        
            }
    }
    
    $dinner = 0;
    if(isset($_POST["dinner"])) {
        
        if($_POST["dinner"] === "1") {
         
            $dinner = 1;
        
        } else {

            die("Opzione non valida.");
        
            }
    }

    $idMenu = null;
    if(isset($_POST["idMenu"])){
        if($_POST["idMenu"] !== ""){

            $idMenu = filter_var($_POST["idMenu"], FILTER_VALIDATE_INT);

            if ($idMenu === false || $idMenu <= 0) {
            
                die("Opzione menu non valida.");

        }

        } 

        if($dinner === 0 && $idMenu !== null) {
            die("Per scegliere un menu fisso è necessario selezionare la cena.");
        }
}
        require_once __DIR__ . "/includes/db.php";
        // Controlla se l'ID del menu selezionato esiste nel database
        if ($idMenu !== null) {
            
            $query = "SELECT idMenu FROM menus WHERE idMenu = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $idMenu);
            $stmt->execute();
            $menuResult = $stmt->get_result();

            if ($menuResult->num_rows === 0) {
                die("Menu selezionato non disponibile.");
    }
}
    // Utente dalla sessione autenticata, camera e date dalla bozza: non dal POST.
    $idUser = $_SESSION["idUser"];
    $pendingBooking = $_SESSION["pendingBooking"];
    $idRoom = $pendingBooking["idRoom"];
    $numGuests = $pendingBooking["numGuests"];
    $checkIn = $pendingBooking["checkIn"];
    $checkOut = $pendingBooking["checkOut"];

    $todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
    $today = $todayDate->format("Y-m-d");

    if ($checkIn < $today) {
        die("La data di check-in non può essere nel passato.");
    }
    
    
    $status = 1; // Imposta lo stato della prenotazione a "confermata"
    
    // Il lock serializza le conferme della stessa camera: controllo e INSERT
    // condividono la transazione per evitare prenotazioni sovrapposte.
    try{

        $connection->begin_transaction();
        $lockQuery = "SELECT idRoom FROM rooms WHERE idRoom = ? FOR UPDATE";
        $lockStmt = $connection->prepare($lockQuery);
        $lockStmt->bind_param("i", $idRoom);
        $lockStmt->execute();
        $lockResult = $lockStmt->get_result();

           $availabilityQuery = "SELECT idRoom, roomNumber, capacity, pricePerNight, description, imagePath
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


        $stmt = $connection->prepare($availabilityQuery);

        $stmt->bind_param("iiss", $idRoom, $numGuests, $checkOut, $checkIn);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $connection->rollback();
            die("La camera non è disponibile per il soggiorno selezionato. Ripeti la ricerca.");
        }
        $room = $result->fetch_assoc();
    
        $checkInDate = new DateTimeImmutable($checkIn);
        $checkOutDate = new DateTimeImmutable($checkOut);
        $nights = $checkInDate->diff($checkOutDate)->days;
        // Il prezzo viene ricalcolato sul server, senza fidarsi del totale nel browser.
        $totalPrice = $nights * $room["pricePerNight"];
        if($breakfast === 1){

            $totalPrice = $totalPrice + ($nights * 8 * $numGuests);

        }
        $bookingQuery = "INSERT INTO bookings (idUser, idRoom, numGuests, checkIn, checkOut, status, totalPrice, breakfast, dinner, idMenu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($bookingQuery);
        $stmt->bind_param("iiissidiii", $idUser, $idRoom, $numGuests, $checkIn, $checkOut, $status, $totalPrice, $breakfast, $dinner, $idMenu);
        $stmt->execute();
        // La bozza viene rimossa soltanto dopo il commit, evitando conferme duplicate.
        $connection->commit();
        $_SESSION["successMessage"] = "Prenotazione confermata!";
        unset($_SESSION["pendingBooking"]);
        header("Location:account.php");
        exit();
    }  catch (mysqli_sql_exception $error) {
            // Annulla le modifiche non confermate
            $connection->rollback();
            die("Prenotazione non riuscita");
    } 

?>
