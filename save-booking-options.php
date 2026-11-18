<?php 

session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST"){

    header("Location:booking.php");
    exit();

    }

    if (!isset($_SESSION["idUser"])) {
        // Se l'utente non è loggato, reindirizza alla pagina di login.
        header("Location: login.php");
        exit();
    }

    if (!isset($_SESSION["pendingBooking"])) {
        // Se non ci sono dati di prenotazione in sospeso, reindirizza alla pagina delle camere.
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

    $breakfast = 0;
    // Controlla se l'utente ha selezionato l'opzione per la colazione
    if(isset($_POST["breakfast"])) {

        if($_POST["breakfast"] === "1") {
         
            $breakfast = 1;
        
        } else {

            die("Opzione non valida.");
        
            }
    }
    
    $dinner = 0;
    // Controlla se l'utente ha selezionato l'opzione per la cena
    if(isset($_POST["dinner"])) {
        
        if($_POST["dinner"] === "1") {
         
            $dinner = 1;
        
        } else {

            die("Opzione non valida.");
        
            }
    }

    $idMenu = null;
    // Controlla se l'utente ha selezionato un menu fisso
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

    // Aggiorna solo le opzioni, conservando camera, date, ospiti e token.
    $_SESSION["pendingBooking"]["breakfast"] = $breakfast;
    $_SESSION["pendingBooking"]["dinner"] = $dinner;
    $_SESSION["pendingBooking"]["idMenu"] = $idMenu;

    // Apre la pagina cucina senza confermare la prenotazione.
    header("Location: kitchen.php");
    exit();

?>