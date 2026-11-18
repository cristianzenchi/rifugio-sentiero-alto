<?php 
// Inizia la sessione per poter salvare le informazioni dell'utente loggato
session_start();

if (!isset($_SESSION["loginToken"])) {
    $_SESSION["loginToken"] = bin2hex(random_bytes(32));
}

// Quando l'utente invia il form, i dati vengono inviati tramite POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  // Rifiuta token mancanti o un valore ricevuto che non sia una stringa.
    if (
        !isset($_SESSION["loginToken"], $_POST["loginToken"]) ||
        !is_string($_POST["loginToken"])) 
    {
        die("Richiesta di accesso non valida. Ricarica la pagina.");
    }

    // Rifiuta un token diverso da quello conservato nella sessione.
    if (!hash_equals($_SESSION["loginToken"], $_POST["loginToken"])) {
        die("Richiesta di accesso non valida. Ricarica la pagina.");
    }

    // Verifica se tutti i campi richiesti sono stati inviati
    if (isset($_POST["email"], $_POST["password"])){
      
      // Assegna i valori ricevuti a variabili
      $email = $_POST["email"];
      $password = $_POST["password"];
        // Interrompe lo script se almeno uno dei valori non è una stringa
        if ( !is_string($email) || !is_string($password)) {
            die("Dati di accesso non validi");
        }

    // Rimuove gli spazi iniziali e finali dall'email
    $email = trim($email);

    // Rifiuta email o password vuote
    if ($email === "" || $password === ""){

      die("Email e password non possono essere vuoti");

    }
    $emailValidated = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($emailValidated === false) {
        die("Indirizzo email non valido");
    }

    // collega il file che contiene la connessione al database
    require_once __DIR__ . "/includes/db.php";

    $query = "SELECT idUser, name, password, role FROM users WHERE email = ?";
    
     /* 
        Prepara l'istruzione SQL e conserva in $stmt l'oggetto
        con cui assoceremo i parametri ed eseguiremo la query.
     */ 
    $stmt = $connection->prepare($query);

    /* 
        Associa il valore al "?".
        "s" indica il tipo: in questo caso, una stringa (s).
    */
    $stmt->bind_param("s", $email);

     // Esegue lo statement usando i valori associati.
    $stmt->execute();

    // Recupera il risultato della ricerca dell'utente.
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Email o password non corrette");
    }

    // Recupera la riga del risultato come array associativo.
    $user = $result->fetch_assoc();

    // Verifica la password inserita confrontandola con l'hash salvato nel database.
    if (!password_verify($password, $user["password"])) {
        die("Email o password non corrette");
    }
    // Dopo il login riuscito, rinnova l'ID di sessione per contrastare la session fixation.
    session_regenerate_id(true);
    $_SESSION["idUser"] = $user["idUser"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["role"] = $user["role"];

    // Verifica se c'è una prenotazione in sospeso nella sessione
    if (isset($_SESSION["pendingBooking"])) {
        // Se c'è una prenotazione in sospeso, reindirizza alla pagina di prenotazione
        header("Location: booking.php");
    } else {
        // Altrimenti, reindirizza alla pagina dell'account
        header("Location: account.php");
    }
    exit();
  }
}


?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accedi</title>
    <link rel="stylesheet" href="./css/styles.css?v=2" />
  </head>
  <body>
    <?php require_once __DIR__ . "/includes/nav.php"; ?>
    <main class="auth-page">
      <h1>Accedi</h1>

      <form action="login.php" method="post" class="auth-form">
        <input type="hidden" name="loginToken"
       value="<?php echo $_SESSION['loginToken']; ?>">
        <div class="form-field">
          <label for="email">Email</label>
            <input
        type="email"
        name="email"
        id="email"
        autocomplete="username"
        required
            />
        </div>
        <div class="form-field">
          <label for="password">Password</label>
            <input
        type="password"
        name="password"
        id="password"
        autocomplete="current-password"
        required
        />
        </div>

        <button type="submit" class="button-primary">Accedi</button>
      </form>

    <p>Non hai un account? <a href="register.php">Registrati</a></p>
    </main>
  </body>
</html>
