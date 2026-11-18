<?php 
session_start();
// collega il file che contiene la connessione al database
    require_once __DIR__ . "/includes/db.php";

    $query = "SELECT idMenu, name, description, dishes, pricePerMenu FROM menus ORDER BY idMenu";
    $menusResult = $connection->query($query);
?>

<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="La cucina del Rifugio Sentiero Alto: colazione, piatti di montagna e due menu fissi. Offerta inventata per un progetto dimostrativo." />
    <title>Cucina | Rifugio Sentiero Alto</title>
    <link rel="stylesheet" href="./css/styles.css?v=3" />
  </head>
  <body>
    <header class="site-header">
      <?php require_once __DIR__ . "/includes/nav.php"; ?>
    </header>

    <main id="main-content" class="kitchen-page">
      <h1>La cucina del rifugio</h1>
      <p>Una tavola semplice, piatti caldi e sapori di montagna. Dopo i sentieri, ci ritroviamo a cena con una proposta raccolta e senza fretta.</p>
      <p><a href="#menu-fissi">Menu fissi</a> · <a href="#menu-carta">Menu alla carta</a> · <a href="#colazione">Colazione</a></p>

      <section id="menu-fissi" aria-labelledby="fixed-menu-title">
        <?php if (isset($_SESSION["idUser"], $_SESSION["pendingBooking"])): ?>
        <p><a href="booking.php">Torna alla prenotazione</a></p>
        <?php endif; ?>
        <h2 id="fixed-menu-title">Due menu fissi per la sera dell'arrivo</h2>
        <p>Puoi scegliere un menu durante la prenotazione del soggiorno. La scelta è facoltativa, vale per tutti gli ospiti della camera e riguarda soltanto la cena del giorno di arrivo.</p>

        <div class="fixed-menu-list">
          <?php while ($menu = $menusResult->fetch_assoc()): ?>
          <article class="fixed-menu">
            <h3><?php echo htmlspecialchars($menu["name"], ENT_QUOTES, "UTF-8"); ?></h3>
            <p><?php echo htmlspecialchars($menu["description"], ENT_QUOTES, "UTF-8"); ?></p>
            <ul>
          <?php
          /* 
            Divide il testo sul ";" e percorre i piatti ottenuti, uno alla volta.
            A ogni giro, $dish contiene un piatto del menu corrente.
          */
          foreach (explode(";", $menu["dishes"]) as $dish):
          ?>
          <li>
            <?php
            /*
               Rimuove gli spazi esterni e mostra il piatto come testo,
               senza interpretare eventuali caratteri HTML.
            */
            echo htmlspecialchars(trim($dish), ENT_QUOTES, "UTF-8");
            ?>
          </li>
           <?php endforeach; ?>
          </ul>
            <p><strong><?php echo number_format($menu["pricePerMenu"], 2, ",", "."); ?> € per persona</strong></p>
          </article>
          <?php endwhile; ?>

         
        </div>

        <p>Se richiedi la cena senza scegliere un menu fisso, deciderai sul posto. Per le sere successive il personale raccoglierà le preferenze durante il soggiorno.</p>
        <p><strong>La cena si paga in struttura e non viene sommata al totale della camera.</strong></p>
        <p><a href="./rooms.php">Cerca un soggiorno e scegli la cena</a></p>
      </section>

      <section id="menu-carta" aria-labelledby="a-la-carte-title">
        <h2 id="a-la-carte-title">Un piccolo menu alla carta</h2>
        <p>Piatti scelti al tavolo, senza prenotazione delle singole portate attraverso il sito. Prezzi e ricette sono inventati per la demo.</p>

        <h3>Primi</h3>
        <dl class="dish-list">
          <dt>Pizzoccheri della casa · 14,00 €</dt>
          <dd>Pasta di grano saraceno con patate, verza e formaggio.</dd>
          <dt>Zuppa di verdure e orzo · 9,00 €</dt>
          <dd>Una zuppa calda con verdure e cereali.</dd>
        </dl>

        <h3>Secondi e piatti unici</h3>
        <dl class="dish-list">
          <dt>Spezzatino di manzo con polenta · 18,00 €</dt>
          <dd>Una proposta sostanziosa per la cena dopo una giornata all'aperto.</dd>
          <dt>Polenta con funghi e formaggio · 13,00 €</dt>
          <dd>La nostra alternativa vegetariana, servita calda.</dd>
        </dl>

        <h3>Dolci</h3>
        <dl class="dish-list">
          <dt>Crostata ai frutti di bosco · 6,00 €</dt>
          <dd>Una fetta di crostata per chiudere la cena.</dd>
          <dt>Torta di mele · 6,00 €</dt>
          <dd>Un dolce semplice da accompagnare a una bevanda calda.</dd>
        </dl>
      </section>

      <section id="colazione" aria-labelledby="breakfast-title">
        <h2 id="breakfast-title">La colazione, prima di ripartire</h2>
        <p>Pane, confetture, yogurt, un dolce e bevande calde: una pausa prima di rimettersi in cammino.</p>
        <p><strong>8,00 € per persona a notte.</strong> Puoi aggiungerla durante la prenotazione; il supplemento vale per tutti gli ospiti e tutte le mattine del soggiorno.</p>
        <p>È compresa anche la mattina del checkout. La colazione viene sommata al totale del soggiorno, mentre la cena resta separata.</p>
      </section>

      <section class="dinner-info" aria-labelledby="dinner-info-title">
        <h2 id="dinner-info-title">Come funziona la cena</h2>
        <ul>
          <li>Il pranzo non è previsto nell'offerta del sito.</li>
          <li>La cena si può richiedere online soltanto insieme a un soggiorno.</li>
          <li>Le preferenze inviate online riguardano la prima sera.</li>
          <li>La scelta della cena e dell'eventuale menu vale per l'intero gruppo della camera.</li>
          <li>Le richieste dei clienti esterni e le cene successive vengono gestite sul posto.</li>
        </ul>
        <p>Per ingredienti, allergeni ed esigenze alimentari è necessario confrontarsi con il personale prima della scelta.</p>
      </section>

      <section aria-labelledby="stay-title">
        <h2 id="stay-title">Una cena, poi il riposo</h2>
        <p>Il soggiorno comincia dalla camera: scegli date e numero di ospiti, verifica la disponibilità e aggiungi i servizi nel riepilogo.</p>
        <p><a href="./rooms.php">Scopri le camere</a></p>
        <p><a href="./index.php#contatti">Informazioni e contatti</a></p>
      </section>
    </main>

    <footer class="site-footer">
      <p>Rifugio Sentiero Alto · Progetto dimostrativo per portfolio.</p>
      <p>Struttura, menu e prezzi sono inventati. Nessun pagamento viene eseguito online.</p>
    </footer>
  </body>
</html>

