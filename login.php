<?php 
session_start();

$errors = [];
$email = "";

if (!isset($_SESSION["loginToken"])) {
    $_SESSION["loginToken"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Rifiuta token mancanti o un valore ricevuto che non sia una stringa.
    if (
        !isset($_SESSION["loginToken"], $_POST["loginToken"]) ||
        !is_string($_POST["loginToken"])
    ) {
        die("Richiesta di accesso non valida. Ricarica la pagina.");
    }

    // Rifiuta un token diverso da quello conservato nella sessione.
    if (!hash_equals($_SESSION["loginToken"], $_POST["loginToken"])) {
        die("Richiesta di accesso non valida. Ricarica la pagina.");
    }

    if (isset($_POST["email"], $_POST["password"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];
        // Rifiuta anche array inviati al posto dei campi testuali.
        if (!is_string($email) || !is_string($password)) {
            die("Dati di accesso non validi");
        }

        $email = trim($email);

        if ($email === "") {
            $errors["email"] = "Inserisci l'email.";
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors["email"] = "Indirizzo email non valido.";
        }

        if ($password === "") {
            $errors["password"] = "Inserisci la password.";
        }

        if ($errors === []) {
            require_once __DIR__ . "/includes/db.php";

            $query = "SELECT idUser, name, password, role FROM users WHERE email = ?";

            $stmt = $connection->prepare($query);

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            $user = $result->fetch_assoc();

            // Lo stesso messaggio non rivela se l'email appartiene a un account.
            if ($user === null || !password_verify($password, $user["password"])) {
                $errors["login"] = "Email o password non corrette.";
            } else {
                // Dopo il login riuscito, rinnova l'ID di sessione per contrastare la session fixation.
                session_regenerate_id(true);
                $_SESSION["idUser"] = $user["idUser"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["role"] = $user["role"];

                // Riprende il soggiorno selezionato prima dell'accesso, se presente.
                if (isset($_SESSION["pendingBooking"])) {
                    header("Location: booking.php");
                } else {
                    header("Location: account.php");
                }
                exit();
            }
        }
    } else {
        $errors["login"] = "Inserisci email e password.";
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
        <?php if (isset($errors["login"])) { ?>
          <p class="field-error" id="login-error" role="alert">
            <?php echo htmlspecialchars($errors["login"], ENT_QUOTES, "UTF-8"); ?>
          </p>
        <?php } ?>
        <input type="hidden" name="loginToken"
       value="<?php echo $_SESSION['loginToken']; ?>">
        <div class="form-field">
          <label for="email">Email</label>
            <input
        type="email"
        name="email"
        id="email"
        autocomplete="username"
        value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
        required
        <?php if (isset($errors["email"])) { ?>
            aria-invalid="true"
            aria-describedby="email-error"
        <?php } elseif (isset($errors["login"])) { ?>
            aria-invalid="true"
            aria-describedby="login-error"
        <?php } ?>
            />
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
        autocomplete="current-password"
        required
        <?php if (isset($errors["password"])) { ?>
            aria-invalid="true"
            aria-describedby="password-error"
        <?php } elseif (isset($errors["login"])) { ?>
            aria-invalid="true"
            aria-describedby="login-error"
        <?php } ?>
        />
          <?php if (isset($errors["password"])) { ?>
            <p class="field-error" id="password-error">
              <?php echo htmlspecialchars($errors["password"], ENT_QUOTES, "UTF-8"); ?>
            </p>
          <?php } ?>
        </div>

        <button type="submit" class="button-primary">Accedi</button>
      </form>

    <p>Non hai un account? <a href="register.php">Registrati</a></p>
    </main>
  </body>
</html>
