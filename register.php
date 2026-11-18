<?php

$errors = [];

$name = "";
$surname = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["name"], $_POST["surname"], $_POST["email"], $_POST["password"], $_POST["confirmPassword"])){
      
      $name = $_POST["name"];
      $surname = $_POST["surname"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirmPassword = $_POST["confirmPassword"];
        // Rifiuta anche array inviati al posto dei campi testuali.
        if (!is_string($name) || !is_string($surname) || !is_string($email) || !is_string($password) || !is_string($confirmPassword)) {
            die("Dati di registrazione non validi");
        }

    $name = trim($name);
    $surname = trim($surname);
    $email = trim($email);

  // Gli errori sono associati ai campi senza interrompere la visualizzazione del form.
   if ($name === "") {
    $errors["name"] = "Inserisci il nome.";
}

if ($surname === "") {
    $errors["surname"] = "Inserisci il cognome.";
}

if ($email === "") {
    $errors["email"] = "Inserisci l'email.";
}
    
    $emailValidated = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($email !== "" && $emailValidated === false) {
        $errors["email"] = "Indirizzo email non valido.";
    }

// Il minimo usa caratteri; il massimo usa byte, per rispettare il limite di bcrypt.
if (trim($password) === "") {
    $errors["password"] = "La password non può essere vuota o composta soltanto da spazi.";
} elseif (mb_strlen($password, "UTF-8") < 15 || strlen($password) > 72) {
    $errors["password"] = "La password deve contenere almeno 15 caratteri e non superare 72 byte.";
}

if ($password !== $confirmPassword) {
    $errors["confirmPassword"] = "Le password non corrispondono.";
}

if (mb_strlen($name, "UTF-8") > 45) {
    $errors["name"] = "Il nome non può superare 45 caratteri.";
}

if (mb_strlen($surname, "UTF-8") > 45) {
    $errors["surname"] = "Il cognome non può superare 45 caratteri.";
}

if (mb_strlen($email, "UTF-8") > 254) {
    $errors["email"] = "l'email non può superare 254 caratteri.";
}

 if($errors === []){
// Nel database conserviamo l'hash, mai la password in chiaro.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

  require_once __DIR__ . "/includes/db.php";

  $query = "INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)";

    $stmt = $connection->prepare($query);

    $stmt->bind_param("ssss", $name, $surname, $email, $passwordHash);

   try {
    $stmt->execute();
    header("Location: login.php");
  exit();
   
} catch (mysqli_sql_exception $error) {
      
    // Il vincolo univoco sull'email gestisce anche registrazioni concorrenti.
    if ($error->getCode() === 1062) {
        $errors["email"] = "Email già registrata. Inserisci un indirizzo email diverso.";
    } else {
        die("Registrazione non riuscita.");
    }

  }
 }
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
            <input
                type="text"
                name="name"
                id="name"
                placeholder="Mario"
                value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                required
                <?php if (isset($errors["name"])) { ?>
                    aria-invalid="true"
                    aria-describedby="name-error"
                <?php } ?>
            />
            <?php if (isset($errors["name"])) { ?>
                <p class="field-error" id="name-error">
                    <?php echo htmlspecialchars($errors["name"], ENT_QUOTES, "UTF-8"); ?>
                </p>
            <?php } ?>
        </div>
        <div class="form-field">
            <label for="surname">Cognome</label>
            <input
                type="text"
                name="surname"
                id="surname"
                placeholder="Rossi"
                value="<?php echo htmlspecialchars($surname, ENT_QUOTES, 'UTF-8'); ?>"
                required
                <?php if (isset($errors["surname"])) { ?>
                    aria-invalid="true"
                    aria-describedby="surname-error"
                <?php } ?>
            />
            <?php if (isset($errors["surname"])) { ?>
                <p class="field-error" id="surname-error">
                    <?php echo htmlspecialchars($errors["surname"], ENT_QUOTES, "UTF-8"); ?>
                </p>
            <?php } ?>
        </div>     
        <div class="form-field">
            <label for="email">Email</label>
             <input type="email" name="email" id="email" placeholder="mario.rossi@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required <?php if (isset($errors["email"])) { ?>
            aria-invalid="true"
            aria-describedby="email-error"
            <?php } ?>/>
             <?php if (isset($errors["email"])) { ?>
            <p class="field-error" id="email-error">
            <?php echo htmlspecialchars($errors["email"], ENT_QUOTES, "UTF-8"); ?>
            </p>
            <?php } ?>
        </div>
        <div class="form-field">
           <label for="password">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                required
                <?php if (isset($errors["password"])) { ?>
                    aria-invalid="true"
                    aria-describedby="password-error"
                <?php } ?>
/>
            <?php if (isset($errors["password"])) { ?>
            <p class="field-error" id="password-error">
            <?php echo htmlspecialchars($errors["password"], ENT_QUOTES, "UTF-8"); ?>
            </p>
            <?php } ?>
        </div>
        <div class="form-field">
            <label for="confirmPassword">Conferma password</label>
             <input
                type="password"
                name="confirmPassword"
                id="confirmPassword"
                required
                <?php if (isset($errors["confirmPassword"])) { ?>
                    aria-invalid="true"
                    aria-describedby="confirm-password-error"
                <?php } ?>
/>
             <?php if (isset($errors["confirmPassword"])) { ?>
            <p class="field-error" id="confirm-password-error">
            <?php echo htmlspecialchars($errors["confirmPassword"], ENT_QUOTES, "UTF-8"); ?>
            </p>
            <?php } ?>
        </div>
        <button type="submit" class="button-primary">Registrati</button>
    </form>
    </main>
  </body>
</html>
