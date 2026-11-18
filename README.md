# Rifugio Sentiero Alto

Applicazione web per cercare e prenotare una camera in un rifugio di montagna immaginario. Progetto di apprendimento e portfolio, realizzato con HTML, CSS, JavaScript, PHP e MySQL, senza framework.

Struttura, prezzi, menu e recapiti sono inventati. Nessuna prenotazione riguarda un soggiorno reale e il sito non raccoglie pagamenti.

## Funzionalità

- Consultazione delle cinque camere e dei menu, anche senza account.
- Ricerca delle camere disponibili per date e numero di ospiti.
- Registrazione, accesso e uscita dall'account.
- Conservazione del soggiorno selezionato durante il passaggio dal login o dalla registrazione.
- Prenotazione con colazione facoltativa e richiesta della cena per la sera dell'arrivo.
- Area personale con storico delle proprie prenotazioni e annullamento fino al giorno precedente l'arrivo.
- Layout responsive per desktop e dispositivi mobili.

JavaScript abilita la scelta del menu quando viene selezionata la cena e aggiorna il totale visualizzato quando si aggiunge o si rimuove la colazione. PHP esegue comunque i controlli e calcola il prezzo definitivo sul server.

## Regole della prenotazione

- Si prenota l'intera camera, non il singolo posto letto.
- La disponibilità esclude le prenotazioni confermate con date sovrapposte. Una partenza e un nuovo arrivo nello stesso giorno sono consentiti.
- La colazione costa 8 euro per persona a notte e, se scelta, vale per tutti gli ospiti e per l'intero soggiorno.
- La cena riguarda soltanto la sera dell'arrivo e tutti gli ospiti della camera. Si può scegliere un menu fisso oppure decidere sul posto; il suo prezzo non viene aggiunto al totale del soggiorno.
- Le prenotazioni annullate rimangono nello storico e non occupano più la camera.

## Avvio in locale

Ambiente usato durante lo sviluppo: MAMP su Windows, PHP 8.3.1 e MySQL 5.7.24. Sono necessarie le estensioni PHP `mysqli` con supporto `mysqlnd` per `get_result()`, e `mbstring`.

1. Posizionare la cartella del progetto nella document root del server web. Con MAMP, la cartella usata durante lo sviluppo è `C:\MAMP\htdocs\webApp`.
2. Avviare il server web e MySQL.
3. Creare un database vuoto chiamato `mountainhutdb` e selezionarlo in phpMyAdmin.
4. Importare il file [`database/mountainhutdb.sql`](database/mountainhutdb.sql). Il file crea le tabelle e inserisce cinque camere e due menu; non contiene account o prenotazioni di prova. Non va importato sopra un database già popolato.
5. Creare `includes/config.local.php` partendo da [`includes/config.example.php`](includes/config.example.php) e impostare host, utente, password e nome del proprio database. Il file locale è escluso da Git tramite `.gitignore`.
6. Aprire [http://localhost/webApp/](http://localhost/webApp/), adattando porta e percorso alla propria configurazione. Aprire il progetto tramite il server web, non come file HTML.
7. Creare un account dalla pagina di registrazione. La password deve avere almeno 15 caratteri e non superare 72 byte; per provare il progetto usare dati inventati e una password non riutilizzata altrove.

Non è richiesto un passaggio di compilazione o l'installazione di dipendenze tramite npm o Composer. GitHub ospita il codice: per eseguire l'applicazione serve un server con PHP e MySQL, non il solo hosting statico.

## Organizzazione del codice

| Percorso                                  | Responsabilità                                                        |
| ----------------------------------------- | --------------------------------------------------------------------- |
| `index.php`, `rooms.php`, `kitchen.php`   | Presentazione, ricerca delle camere e consultazione dei menu          |
| `register.php`, `login.php`, `logout.php` | Registrazione e gestione dell'accesso                                 |
| `booking.php`                             | Bozza e riepilogo del soggiorno selezionato                           |
| `save-booking-options.php`                | Salvataggio delle opzioni prima di consultare la cucina               |
| `confirm-booking.php`                     | Controllo finale della disponibilità e inserimento della prenotazione |
| `account.php`, `cancel-booking.php`       | Prenotazioni dell'utente e annullamento                               |
| `includes/`                               | Navigazione condivisa, connessione e configurazione del database      |
| `css/`, `js/`, `images/`                  | Stili, interazioni e immagini                                         |
| `database/mountainhutdb.sql`              | Struttura del database e dati iniziali                                |

Il database contiene le tabelle `users`, `rooms`, `menus` e `bookings`. Il codice usa query parametrizzate per i valori ricevuti, hash delle password e controlli di proprietà delle prenotazioni. La conferma esegue un nuovo controllo della disponibilità all'interno di una transazione con blocco della riga della camera.

## Verifiche e limiti

Durante lo sviluppo sono state effettuate prove manuali su ricerca, accesso, prenotazione, colazione, annullamento, token alterati, separazione dei dati tra account e disponibilità dopo una prenotazione effettuata da un'altra sessione. Il layout è stato verificato a diverse larghezze, tra cui 320, 390 e 820 pixel e desktop. Non è presente una suite automatizzata end-to-end.

Il progetto non include pagamenti online, pannello amministrativo o invio di email. È una demo didattica, non un servizio pronto per gestire prenotazioni reali; una messa in produzione richiede ulteriori verifiche e la configurazione del server.

## Immagini

Le immagini sono illustrative e non rappresentano un'unica struttura reale. Sono presenti fotografie e immagini generate con AI; fonti e dettagli sono raccolti in [`images/rooms/CREDITS.md`](images/rooms/CREDITS.md).

Il link alla demo verrà aggiunto quando il sito sarà online.
