<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Rifugio Sentiero Alto: cinque camere, cucina di montagna e un soggiorno tra boschi e sentieri. Progetto dimostrativo." />
    <title>Rifugio Sentiero Alto</title>
    <link rel="stylesheet" href="./css/styles.css?v=2" />
  </head>
  <body>
    <header class="site-header">
      <?php require_once __DIR__ . "/includes/nav.php"; ?>
    </header>

    <main id="main-content" class ="home-page">
      <section class="home-intro" aria-labelledby="intro-title">
        <div class="home-hero">
          <h1 id="intro-title">Rifugio Sentiero Alto</h1>
          <p>Una camera tranquilla, un piatto caldo e il tempo di ritrovare il proprio passo.</p>
        </div>
        <p>Un piccolo rifugio immaginario sulle Alpi, tra boschi e sentieri. Qui la giornata comincia con la colazione e finisce intorno a un tavolo, prima di un'altra notte in montagna.</p>

        <form class="search-form" action="./rooms.php" method="get">
            <div class="form-field">
              <label for="homeCheckIn">Arrivo</label>
              <input type="date" name="checkIn" id="homeCheckIn" required />
            </div>
            <div class="form-field">
              <label for="homeCheckOut">Partenza</label>
              <input type="date" name="checkOut" id="homeCheckOut" required />
            </div>
            <div class="form-field">
              <label for="homeNumGuests">Numero di ospiti</label>
              <input type="number" name="numGuests" id="homeNumGuests" min="1" max="3" required />
            </div>
            <button type="submit" class="button-primary">Cerca disponibilità</button>
        </form>
        <p><a href="./rooms.php">Scopri tutte le camere</a></p>
      </section>

      <section id="chi-siamo" aria-labelledby="about-title">
        <h2 id="about-title">Chi siamo</h2>
        <p>Sentiero Alto nasce dall'idea di un'ospitalità essenziale: pochi posti, ambienti semplici e attenzione ai ritmi di chi cammina. Ci immaginiamo una casa di montagna dove fermarsi dopo un'escursione o trascorrere qualche giorno lontano dalla fretta.</p>
        <p>Non servono programmi complicati: una passeggiata nel bosco, una cena condivisa e una stanza in cui riposare. Il resto può aspettare.</p>
      </section>

      <section id="camere" aria-labelledby="rooms-title">
        <h2 id="rooms-title">Cinque camere, nove posti letto</h2>
        <p>Due singole, due doppie e una tripla, con arredi in legno e un'atmosfera raccolta. Ogni prenotazione riserva l'intera camera: una soluzione per chi viaggia da solo, in coppia o in un piccolo gruppo.</p>
        <ul>
          <li><strong>Singole:</strong> uno spazio tutto tuo per ripartire riposato.</li>
          <li><strong>Doppie:</strong> letto matrimoniale oppure due letti singoli.</li>
          <li><strong>Tripla:</strong> un letto matrimoniale e un letto singolo.</li>
        </ul>
        <p>Puoi consultare le camere e cercare le date anche senza account. L'accesso viene richiesto quando scegli il soggiorno da prenotare.</p>
        <p><a href="./rooms.php">Trova la camera per te</a></p>
      </section>

      <section id="cucina" aria-labelledby="food-title">
        <h2 id="food-title">Il piacere di fermarsi a tavola</h2>
        <p>Polenta, zuppe, piatti della tradizione e un dolce per chiudere la giornata. La nostra proposta immaginaria comprende un piccolo menu alla carta e due menu fissi, uno dei quali vegetariano.</p>
        <p>La colazione è facoltativa e si aggiunge al soggiorno. Per la sera dell'arrivo puoi richiedere la cena ed eventualmente scegliere in anticipo un menu fisso.</p>
        <p><a href="./kitchen.php">Scopri la cucina e i menu</a></p>
      </section>

      <section id="attivita" aria-labelledby="outdoors-title">
        <h2 id="outdoors-title">Una giornata al tuo ritmo</h2>
        <p>Il rifugio è pensato come punto di partenza per camminare e come luogo in cui fermarsi. Passeggiate tra gli alberi, pause panoramiche e giornate dedicate al riposo fanno parte dello stesso soggiorno.</p>
        <p>Le attività sono raccontate come parte dell'ambientazione: non sono previsti servizi di guida o prenotazioni di escursioni attraverso il sito.</p>
      </section>

      <section id="informazioni" aria-labelledby="info-title">
        <h2 id="info-title">Informazioni per il soggiorno</h2>
        <dl>
          <dt>Arrivo</dt>
          <dd>Check-in dalle 15:00 alle 19:00.</dd>
          <dt>Partenza</dt>
          <dd>Check-out entro le 10:00.</dd>
          <dt>Colazione</dt>
          <dd>Dalle 7:30 alle 9:00, su richiesta in fase di prenotazione.</dd>
          <dt>Cena</dt>
          <dd>Dalle 19:30 alle 20:30. Le preferenze per le sere successive all'arrivo vengono raccolte al rifugio.</dd>
          <dt>Pagamento</dt>
          <dd>In struttura. Il sito dimostrativo non raccoglie carte di credito e non esegue pagamenti.</dd>
        </dl>
      </section>

      <section id="domande-frequenti" aria-labelledby="faq-title">
        <h2 id="faq-title">Domande frequenti</h2>
        <details>
          <summary>Devo creare un account per vedere le disponibilità?</summary>
          <p>No. Puoi cercare date e camere liberamente. Ti chiederemo di accedere o registrarti dopo aver scelto una camera; il soggiorno selezionato viene conservato durante il passaggio.</p>
        </details>
        <details>
          <summary>La colazione è inclusa?</summary>
          <p>È facoltativa e costa 8 euro per persona a notte. Se la aggiungi, vale per tutti gli ospiti e tutte le mattine del soggiorno, compresa quella della partenza.</p>
        </details>
        <details>
          <summary>Posso scegliere la cena in anticipo?</summary>
          <p>Sì, per la sera dell'arrivo e per tutti gli ospiti della camera. Puoi richiedere la cena senza menu fisso oppure scegliere uno dei due menu proposti. La cena si paga separatamente in struttura.</p>
        </details>
        <details>
          <summary>Come annullo una prenotazione?</summary>
          <p>Accedi all'area personale. Puoi annullare una prenotazione confermata fino al giorno precedente l'arrivo. La prenotazione rimane nello storico con lo stato aggiornato.</p>
        </details>
      </section>

      <section id="contatti" aria-labelledby="contact-title">
        <h2 id="contact-title">Contatti</h2>
        <address>
          Rifugio Sentiero Alto<br />
          Località Pian del Bosco, Alpi italiane<br />
          Email dimostrativa: info@sentieroalto.example
        </address>
        <p>Nome, ambientazione, orari e recapiti sono inventati per questo progetto. Non corrispondono a una struttura da contattare o raggiungere.</p>
        <p>Per consultare un soggiorno già inserito nel progetto, usa l'<a href="./account.php">area personale</a>.</p>
      </section>
    </main>

    <footer class="site-footer">
      <p>Rifugio Sentiero Alto · Progetto dimostrativo per portfolio.</p>
      <p>Struttura immaginaria: nessuna prenotazione riguarda un soggiorno reale.</p>
    </footer>
  </body>
</html>

