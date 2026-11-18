<?php

// Richiama la sessione esistente o ne crea una nuova se non esiste.
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    
    // Reindirizza all'area personale
    header("Location: account.php");
    exit();
}

  // Rifiuta token mancanti o un valore ricevuto che non sia una stringa.
    if (
        !isset($_SESSION["logoutToken"], $_POST["logoutToken"]) ||
        !is_string($_POST["logoutToken"])) 
    {
        die("Richiesta di uscita non valida. Ricarica l'area personale.");
    }

    // Rifiuta un token diverso da quello conservato nella sessione.
    if (!hash_equals($_SESSION["logoutToken"], $_POST["logoutToken"])) {
        die("Richiesta di uscita non valida. Ricarica l'area personale.");
    }


$_SESSION = [];
session_destroy();



// Se la sessione usa un cookie, chiede al browser di eliminarlo.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

header("Location: login.php");
exit();

?>