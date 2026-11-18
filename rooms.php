<?php
// collega il file che contiene la connessione al database
require_once __DIR__ . "/includes/db.php";

    // La query SQL per leggere le camere dal database.
    $query = "SELECT roomNumber, capacity, pricePerNight, description, imagePath FROM rooms ";

    $searchPerformed = false; // Variabile per tenere traccia se è stata effettuata una ricerca

    // Legge il numero di ospiti soltanto se i parametri sono presenti nell'URL. 
    if(isset($_GET["numGuests"], $_GET["checkIn"], $_GET["checkOut"])) {
    // Assegna le date di check-in e check-out.
    $checkIn = $_GET["checkIn"];
    $checkOut = $_GET["checkOut"];
    /* 
        Controlla che le date siano nel formato corretto (YYYY-MM-DD). 
        Tramite preg_match() verifica che la stringa sia composta esattamente da quattro cifre,
        un trattino, due cifre, un trattino e due cifre.
        Il controllo del formato, da solo, non rileva date inesistenti come 2024-02-31.   
    */
    $checkInValid = is_string($checkIn)
    && preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $checkIn) === 1;
    $checkOutValid = is_string($checkOut)
    && preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $checkOut) === 1;

    // Verifica che le date siano valide
    if($checkInValid){

        [
            $year, $month, $day
        ] = explode("-", $checkIn); // Divide la stringa in tre parti separate dal trattino.

        $checkInValid = checkdate((int)$month, (int)$day, (int)$year); // Controlla che la data esista davvero.

    }

       if($checkOutValid){

        [
            $year, $month, $day
        ] = explode("-", $checkOut); // Divide la stringa in tre parti separate dal trattino.

        $checkOutValid = checkdate((int)$month, (int)$day, (int)$year); // Controlla che la data esista davvero.

    }
        // Controlla che le date siano valide
        if (!$checkInValid || !$checkOutValid){

            die("Data di check-in o check-out non valida.");

        } else if ($checkIn >= $checkOut) {

            die("La data di check-in deve essere precedente alla data di check-out.");

        }

        $todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
        $today = $todayDate->format("Y-m-d");

        if ($checkIn < $today) {
            die("La data di check-in non può essere nel passato.");
        }

  /*
    filter_var() verifica il valore ricevuto in $_GET["numGuests"].
    La costante PHP FILTER_VALIDATE_INT indica di verificare che rappresenti
    un intero: $numGuests riceve il numero convertito oppure false.
*/
$numGuests = filter_var($_GET["numGuests"], FILTER_VALIDATE_INT);

// Accetta soltanto numeri interi compresi tra 1 e 3.
if ($numGuests !== false && $numGuests >= 1 && $numGuests <= 3) {

    /* 
        Cerca camere con capienza sufficiente, escludendo quelle con
        prenotazioni confermate sovrapposte al soggiorno richiesto.
    */
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

     /* 
        Prepara l'istruzione SQL e conserva in $stmt l'oggetto
        con cui assoceremo i parametri ed eseguiremo la query.
     */ 
    $stmt = $connection->prepare($query);

    /* 
        Associa i valori ai tre ? nell'ordine in cui compaiono nella query.
        "iss" indica i tipi: un intero (i), seguito da due stringhe (s).
    */
    $stmt->bind_param("iss", $numGuests, $checkOut, $checkIn);

    // Esegue lo statement usando i valori associati.
    $stmt->execute();

    // Recupera il risultato, che il ciclo HTML leggera' tramite fetch_assoc().
    $result = $stmt->get_result();
    $searchPerformed = true; // Segna che è stata effettuata una ricerca

} else {

    // Interrompe lo script se il numero di ospiti non e' valido.
    die("Numero di ospiti non valido. Inserire un numero intero tra 1 e 3.");

}


        
} else {
    // Senza ricerca, esegue la SELECT di base definita prima dell'isset().
    $query .= " ORDER BY roomNumber ASC";
    $result = $connection->query($query);
}

?>

<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Titolo della pagina</title>
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
       required/> <!-- "??" significa: "usa $checkIn se esiste e non è null, altrimenti
        usa una stringa vuota". In questo modo all'apertura sarà vuoto e dopo una ricerca manterrà
        la data selezionata invece che resettarsi -->
        </div>
        <div class="form-field">
            <label for="checkOut">Partenza:</label>
                <input type="date" name="checkOut" id="checkOut"
       value="<?php echo htmlspecialchars($checkOut ?? "", ENT_QUOTES, "UTF-8"); ?>"
       required/>
        </div>
        <div class="form-field">
            <label for="numGuests">Numero di ospiti:</label>
                <input type="number" name="numGuests" id="numGuests"
       min="1" max="3"
       value="<?php echo htmlspecialchars((string) ($numGuests ?? ""), ENT_QUOTES, "UTF-8"); ?>"
       required/> <!-- Converte $numGuests in testo, il tipo atteso da htmlspecialchars() -->
        </div> 
       <button type="submit" class="button-primary">Cerca disponibilità</button>
    </form>

    <?php     
// Verifica che sia stata trovata almeno una camera.
if ($result -> num_rows > 0) {

/*   
    fetch_assoc() legge una riga come array associativo.
    le chiavi sono i nomi delle colonne, per esempio "capacity".
    Il ciclo while serve per visualizzare tutte le camere.
*/


    while ($room = $result -> fetch_assoc()){

        // Accedi ai valori dell'array e li concateni
        echo '<article class="room">';
        echo '<img src="' . htmlspecialchars((string) $room["imagePath"], ENT_QUOTES, "UTF-8") . '" alt="Interno della camera ' . $room["roomNumber"] . '">';
        echo '<div class="room-info">'; // Il contenitore serve perché, quando applicherò flexbox a .room, tutto il testo venga trattato come un unico elemento accanto alla foto.
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
