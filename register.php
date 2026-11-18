<?php
// Quando l'utente invia il form, i dati vengono inviati tramite POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verifica se tutti i campi richiesti sono stati inviati
    if (isset($_POST["name"], $_POST["surname"], $_POST["email"], $_POST["password"], $_POST["confirmPassword"])){
      
      // Assegna i valori ricevuti a variabili
      $name = $_POST["name"];
      $surname = $_POST["surname"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirmPassword = $_POST["confirmPassword"];
        // Interrompe lo script se almeno uno dei valori non è una stringa
        if (!is_string($name) || !is_string($surname) || !is_string($email) || !is_string($password) || !is_string($confirmPassword)) {
            die("Dati di registrazione non validi");
        }

    // Rimuove gli spazi iniziali e finali da nome, cognome ed email
    $name = trim($name);
    $surname = trim($surname);
    $email = trim($email);

  // Rifiuta nome, cognome o email vuoti dopo la rimozione degli spazi esterni
    if ($name === "" || $surname === "" || $email === ""){

      die("Nome, cognome ed email sono obbligatori");

    }
    $emailValidated = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($emailValidated === false) {
        die("Indirizzo email non valido");
    }

// Rifiuta password vuote o composte soltanto da spazi.
if (trim($password) === "") {
    die("La password non può essere vuota o composta soltanto da spazi.");
}

// Verifica che la password rispetti i requisiti di lunghezza.
if (mb_strlen($password, "UTF-8") < 15 || strlen($password) > 72) {
    die("La password deve contenere almeno 15 caratteri e non superare 72 byte.");
}

// Verifica che password e conferma siano identiche.
if ($password !== $confirmPassword) {
    die("Le password non corrispondono.");
}

if (mb_strlen($name, "UTF-8") > 45 || mb_strlen($surname, "UTF-8") > 45 || mb_strlen($email, "UTF-8") > 254 ){

    die("Dati di registrazione troppo lunghi");

  }

// Genera l'hash che salveremo nel database al posto della password originale
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

  // Carica il file che crea la connessione al database
  require_once __DIR__ . "/includes/db.php";

  // Definisce la query SQL per inserire i dati dell'utente nel database
  $query = "INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)";

   /* 
        Prepara l'istruzione SQL e conserva in $stmt l'oggetto
        con cui assoceremo i parametri ed eseguiremo la query.
   */ 
    $stmt = $connection->prepare($query);

    /* 
        Associa i valori ai ? nell'ordine in cui compaiono nella query.
        "ssss" indica i tipi: in questo caso, 4 stringhe (s).
    */
    $stmt->bind_param("ssss", $name, $surname, $email, $passwordHash);

   try {
    // Tenta di inserire l'utente nel database.
    $stmt->execute();

} catch (mysqli_sql_exception $error) {
    /* 
        Intercetta un errore MySQLi generato nel blocco try.
        $error contiene l'oggetto con le informazioni sull'errore.
    */
      
    /* 
        Il codice 1062 indica un valore duplicato in una chiave univoca:
        nel nostro INSERT, l'email e' gia' registrata.
    */
    if ($error->getCode() === 1062) {
        die("Email già registrata. Inserire un indirizzo email diverso.");
    } else {
        // Per gli altri errori mostra un messaggio generico.
        die("Registrazione non riuscita.");
    }

  }
  header("Location: login.php");
  exit();
}
}

?>

<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registrazione</title>
    <link rel="stylesheet" href="./css/styles.css?v=2" />
  </head>
  <body>
    <?php require_once __DIR__ . "/includes/nav.php"; ?>
    <main class="auth-page">
    <h1>Crea un account</h1>
    <form action="register.php" method="post" class="auth-form">
        <div class="form-field">
           <label for="name">Nome</label>
            <input type="text" name="name" id="name" placeholder="Mario" required/>
        </div>
        <div class="form-field">
            <label for="surname">Cognome</label>
             <input type="text" name="surname" id="surname" placeholder="Rossi" required/>
        </div>     
        <div class="form-field">
            <label for="email">Email</label>
             <input type="email" name="email" id="email" placeholder="mario.rossi@example.com" required/>
        </div>
        <div class="form-field">
           <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Inserisci la tua password" required/>
        </div>
        <div class="form-field">
            <label for="confirmPassword">Conferma password</label>
             <input type="password" name="confirmPassword" id="confirmPassword" required />
        </div>
        <button type="submit" class="button-primary">Registrati</button>
    </form>
    </main>
  </body>
</html>
