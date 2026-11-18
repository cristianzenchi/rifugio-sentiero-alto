<?php
/*
    Questa istruzione configura MySQLi per segnalare gli errori 
    tramite eccezioni. MYSQLI_REPORT_ERROR: segnala gli errori delle operazioni MySQLi.
    MYSQLI_REPORT_STRICT: fa segnalare quegli errori tramite eccezioni, che possiamo intercettare con catch.
*/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
 
require_once __DIR__ . "/config.local.php";

    try {

    // Crea l'oggetto mysqli e apre la connessione al database.
    $connection = new mysqli(
        $servername,
        $username,
        $dbPassword,
        $databasename
    );

    $connection->set_charset("utf8mb4");

    } catch (mysqli_sql_exception) {

            die ("Servizio temporaneamente non disponibile");

    }

    ?>