<?php
// Usa eccezioni per gli errori MySQLi, anche durante la connessione.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
 
require_once __DIR__ . "/config.local.php";

    try {

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
