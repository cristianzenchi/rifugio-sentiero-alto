<?php

session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST"){

    header("Location:account.php");
    exit();

    }

    if (!isset($_SESSION["idUser"])) {
        // Se l'utente non è loggato, reindirizza alla pagina di login.
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

   // Rifiuta la richiesta se manca l'ID.
    if (!isset($_POST["idBookings"])) {
        die("Prenotazione non valida.");
    }

    // Verifica che il valore rappresenti un intero.
    $idBookings = filter_var($_POST["idBookings"], FILTER_VALIDATE_INT);

    // Rifiuta valori non interi, zero e numeri negativi.
    if ($idBookings === false || $idBookings <= 0) {
        die("Prenotazione non valida.");
    }

    $idUser = $_SESSION["idUser"];
    $todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
    $today = $todayDate->format("Y-m-d");

    require_once __DIR__ . "/includes/db.php";

    $query = "UPDATE bookings SET status = 0 WHERE idBookings = ? AND idUser = ? AND status = 1 AND checkIn > ?";

    try{
    /* 
        Prepara l'istruzione SQL e conserva in $stmt l'oggetto
        con cui assoceremo i parametri ed eseguiremo la query.
    */ 

    $stmt = $connection->prepare($query);

    /* 
        Associa i valori ai tre ? nell'ordine in cui compaiono nella query.
        "iis" indica i tipi: due interi (i), seguiti da una stringa (s).
    */
    $stmt->bind_param("iis", $idBookings, $idUser, $today);

    // Esegue lo statement usando i valori associati.
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