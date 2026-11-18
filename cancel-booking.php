<?php

session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST"){

    header("Location:account.php");
    exit();

    }

    if (!isset($_SESSION["idUser"])) {
        header("Location: login.php");
        exit();
    }

    // Rifiuta token mancanti o un valore ricevuto che non sia una stringa.
    if (
        !isset($_SESSION["cancelToken"], $_POST["cancelToken"]) ||
        !is_string($_POST["cancelToken"])) 
    {
        die("Richiesta non valida. Riapri l'area personale.");
    }

    // Rifiuta un token diverso da quello conservato nella sessione.
    if (!hash_equals($_SESSION["cancelToken"], $_POST["cancelToken"])) {
        die("Richiesta non valida. Riapri l'area personale.");
    }

    if (!isset($_POST["idBookings"])) {
        die("Prenotazione non valida.");
    }

    $idBookings = filter_var($_POST["idBookings"], FILTER_VALIDATE_INT);

    if ($idBookings === false || $idBookings <= 0) {
        die("Prenotazione non valida.");
    }

    $idUser = $_SESSION["idUser"];
    $todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
    $today = $todayDate->format("Y-m-d");

    require_once __DIR__ . "/includes/db.php";

    // Proprietà, stato e data sono verificati dallo stesso UPDATE.
    // Zero righe modificate indica una prenotazione non annullabile, non un errore SQL.
    $query = "UPDATE bookings SET status = 0 WHERE idBookings = ? AND idUser = ? AND status = 1 AND checkIn > ?";

    try{

    $stmt = $connection->prepare($query);

    $stmt->bind_param("iis", $idBookings, $idUser, $today);

    $stmt->execute();
    if($stmt->affected_rows === 1){

        $_SESSION["successMessage"] = "Prenotazione annullata.";
        header("Location: account.php");
        exit;

    }else{
        die ("Prenotazione non annullabile");
        exit;
    }
    } catch (mysqli_sql_exception $error) {
    die("Annullamento non riuscito.");
}

?>
