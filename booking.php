<?php
    // Richiama la sessione esistente o ne crea una nuova se non esiste 
    session_start();
    if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
        // Verifica se i campi richiesti sono stati inviati
        if(isset($_POST["idRoom"], $_POST["numGuests"], $_POST["checkIn"], $_POST["checkOut"])) {
        // Assegna le variabili dai campi ricevuti dal modulo
        $idRoom = $_POST["idRoom"];
        $numGuests = $_POST["numGuests"];
        $checkIn = $_POST["checkIn"];
        $checkOut = $_POST["checkOut"];

        $idRoom = filter_var($idRoom, FILTER_VALIDATE_INT);
        $numGuests = filter_var($numGuests, FILTER_VALIDATE_INT);
        
        if ($idRoom === false || $idRoom <= 0 || $numGuests === false || $numGuests < 1 || $numGuests > 3) {
            die("ID camera o numero di ospiti non validi");
        }

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

        // Conserva la scelta del soggiorno anche durante il passaggio dal login
        $_SESSION["pendingBooking"] = [
        "idRoom" => $idRoom,
        "numGuests" => $numGuests,
        "checkIn" => $checkIn,
        "checkOut" => $checkOut
        ];

        
    } else {
        die("Dati del soggiorno mancanti. Ripeti la ricerca delle camere.");
    }

} 
    /*
        I controlli sull'autenticazione e sulla bozza sono fuori dal blocco POST,
        quindi vengono eseguiti anche nelle richieste GET, come al ritorno dal login.
        I dati del soggiorno vengono recuperati dalla sessione senza un nuovo invio del modulo.
    */
        if( !isset($_SESSION["idUser"])){
        // Se l'utente non è loggato, reindirizza alla pagina di login
        header("Location: login.php");
        exit();
    }

    if( !isset($_SESSION["pendingBooking"])){
        // Se non ci sono dati di prenotazione in sospeso, reindirizza alla pagina delle camere
        header("Location: rooms.php");
        exit();
    }

    $pendingBooking = $_SESSION["pendingBooking"];
    $idRoom = $pendingBooking["idRoom"];
    $numGuests = $pendingBooking["numGuests"];
    $checkIn = $pendingBooking["checkIn"];
    $checkOut = $pendingBooking["checkOut"];

    
    // Recupera le opzioni salvate; alla prima apertura usa i valori predefiniti.
    $breakfast = $pendingBooking["breakfast"] ?? 0;
    $dinner = $pendingBooking["dinner"] ?? 0;
    $idMenu = $pendingBooking["idMenu"] ?? null;


    // Collega il file che contiene la connessione al database
    require_once __DIR__ . "/includes/db.php";
     $query = "SELECT idRoom, roomNumber, capacity, pricePerNight, description, imagePath
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

      /* 
        Prepara l'istruzione SQL e conserva in $stmt l'oggetto
        con cui assoceremo i parametri ed eseguiremo la query.
      */ 

    $stmt = $connection->prepare($query);

    /* 
        Associa i valori ai tre ? nell'ordine in cui compaiono nella query.
        "iiss" indica i tipi: due interi (i), seguiti da due stringhe (s).
    */
    $stmt->bind_param("iiss", $idRoom, $numGuests, $checkOut, $checkIn);

    // Esegue lo statement usando i valori associati.
    $stmt->execute();

    // Recupera il risultato, che il ciclo HTML leggera' tramite fetch_assoc().
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("La camera non è disponibile per il soggiorno selezionato. Ripeti la ricerca.");
    }

    // Recupera la riga del risultato come array associativo.
    $room = $result->fetch_assoc();
    // Crea oggetti DateTimeImmutable per calcolare il numero di notti del soggiorno
    $checkInDate = new DateTimeImmutable($checkIn);
    $checkOutDate = new DateTimeImmutable($checkOut);
    // Calcola la differenza tra le due date e ottiene il numero di giorni
    $nights = $checkInDate->diff($checkOutDate)->days;
    // Calcola il prezzo totale del soggiorno moltiplicando il numero di notti per il prezzo per notte della camera
    $totalPrice = $nights * $room["pricePerNight"];

    $query = "SELECT idMenu, name, pricePerMenu FROM menus ORDER BY idMenu ASC";
    $menusResult = $connection->query($query);

    // Legge la data di oggi per rifiutare un check-in precedente alla data odierna
    $todayDate = new DateTimeImmutable("today", new DateTimeZone("Europe/Rome"));
    $today = $todayDate->format("Y-m-d");

    if ($checkIn < $today) {
        die("La data di check-in non può essere nel passato.");
    }
    
    // Crea un token per questa bozza, se non esiste gia'.
    if (!isset($_SESSION["pendingBooking"]["token"])) {
        $_SESSION["pendingBooking"]["token"] = bin2hex(random_bytes(32));
}

    /*
        Il token serve a proteggere la prenotazione da attacchi CSRF.
        Viene generato una sola volta e conservato nella sessione.
        Quando l'utente invia il modulo di conferma, il token viene inviato
        come campo nascosto e confrontato con quello in sessione.
        Se i due token non corrispondono, la prenotazione viene rifiutata.
    */

?> 

<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Prenotazione</title>
    <link rel="stylesheet" href="./css/styles.css?v=2" />
    <script src="./js/booking.js" defer></script>
  </head>
  <body>
    <?php require_once __DIR__ . "/includes/nav.php"; ?>

    <h1>Conferma prenotazione</h1>
    <h2>Camera <?php echo $room["roomNumber"]; ?></h2>
    <div class="booking-layout">
      <section class="booking-summary">
        <p>Arrivo: <?php echo $checkIn; ?></p>
        <p>Partenza: <?php echo $checkOut; ?></p>
        <p>Numero di ospiti: <?php echo $numGuests; ?></p>
        <p>Numero di notti: <?php echo $nights; ?></p>
        <p id="booking-total" data-base-total="<?php echo $totalPrice ?>"
         data-guests="<?php echo $numGuests ?>" data-nights="<?php echo $nights ?>">
          Totale camera (colazione esclusa):
          <?php echo number_format($totalPrice, 2, ",", "."); ?> €
        </p>
      </section>

      <form action="confirm-booking.php" method="post" class="booking-form">
        <input type="hidden" name="bookingToken"
               value="<?php echo $_SESSION["pendingBooking"]["token"]; ?>" />

        <div class="checkbox-field">
          <input type="checkbox" name="breakfast" value="1" id="breakfast"
                 <?php if ($breakfast === 1) { echo "checked"; } ?> />
          <label for="breakfast">
            Aggiungi colazione (8 € per persona a notte)
          </label>
        </div>

        <div class="checkbox-field">
          <input type="checkbox" name="dinner" value="1" id="dinner"
                 <?php if ($dinner === 1) { echo "checked"; } ?> />
          <label for="dinner">
            Aggiungi la cena per tutti gli ospiti la sera dell’arrivo
            (pagamento in struttura)
          </label>
        </div>

        <div class="form-field"> 
          <label for="idMenu">Menu per la cena d’arrivo</label>
          <select name="idMenu" id="idMenu">
            <option value="">Nessun menu fisso</option>
            <?php while ($menu = $menusResult->fetch_assoc()): ?>
              <option value="<?php echo $menu["idMenu"]; ?>"
                      <?php if ($idMenu === (int) $menu["idMenu"]) { echo "selected"; } ?>>
                <?php
                echo htmlspecialchars($menu["name"], ENT_QUOTES, "UTF-8")
                    . ": " . number_format($menu["pricePerMenu"], 2, ",", ".") . " €";
                ?>
              </option>
            <?php endwhile; ?>
          </select>
          <p>Prezzi per persona. Senza menu fisso, scegli sul posto.</p>
        </div>

        <div class="booking-actions">
          <button type="submit" formaction="save-booking-options.php" class="button-secondary">
            Consulta i menu
          </button>
          <button type="submit" class="button-primary">
            Conferma prenotazione
          </button>
        </div>
      </form>
    </div>
  </body>
</html>