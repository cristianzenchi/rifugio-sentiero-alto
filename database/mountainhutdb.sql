-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Nov 16, 2026 alle 16:30
-- Versione del server: 5.7.24
-- Versione PHP: 8.3.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mountainhutdb`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `bookings`
--

CREATE TABLE `bookings` (
  `idBookings` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `idRoom` int(11) NOT NULL,
  `idMenu` int(11) DEFAULT NULL,
  `numGuests` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL,
  `breakfast` tinyint(4) NOT NULL,
  `dinner` tinyint(4) NOT NULL,
  `checkIn` date NOT NULL,
  `checkOut` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `menus`
--

CREATE TABLE `menus` (
  `idMenu` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `dishes` text NOT NULL,
  `pricePerMenu` decimal(10,2) NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `menus`
--

INSERT INTO `menus` (`idMenu`, `description`, `dishes`, `pricePerMenu`, `name`) VALUES
(1, 'Una cena sostanziosa con sapori della tradizione di montagna.', 'Pizzoccheri; spezzatino di manzo con polenta; crostata ai frutti di bosco.', '28.00', 'Menu del rifugio'),
(2, 'Una proposta senza carne, semplice e ricca di sapore.', 'Zuppa di verdure e orzo; polenta con funghi e formaggio; torta di mele.', '25.00', 'Menu vegetariano');

-- --------------------------------------------------------

--
-- Struttura della tabella `rooms`
--

CREATE TABLE `rooms` (
  `idRoom` int(11) NOT NULL,
  `roomNumber` tinyint(4) NOT NULL,
  `capacity` tinyint(4) NOT NULL,
  `pricePerNight` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `imagePath` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `rooms`
--

INSERT INTO `rooms` (`idRoom`, `roomNumber`, `capacity`, `pricePerNight`, `description`, `imagePath`) VALUES
(1, 1, 1, '40.00', 'Camera singola semplice e accogliente, con letto singolo, arredi in legno e vista sulle montagne. Ideale per riposare dopo una giornata sui sentieri.', 'images/rooms/room1.jpg'),
(2, 2, 1, '40.00', 'Camera singola con arredi in legno e una finestra affacciata sul bosco. Uno spazio tranquillo e confortevole per chi viaggia da solo.', 'images/rooms/room2.jpg'),
(3, 3, 2, '100.00', 'Camera doppia con letto matrimoniale, arredi in legno e vista sulle montagne. Uno spazio semplice e accogliente per riposare in coppia.', 'images/rooms/room3.jpg'),
(4, 4, 2, '90.00', 'Camera doppia con due letti singoli e una finestra affacciata sul bosco. Ideale per amici o compagni di escursione.', 'images/rooms/room4.jpg'),
(5, 5, 3, '150.00', 'Camera tripla con un letto matrimoniale e un letto singolo. Una soluzione pratica e confortevole per una famiglia o un piccolo gruppo.', 'images/rooms/rooms5.jpg');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `idUser` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `surname` varchar(45) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'guest'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`idBookings`),
  ADD KEY `fkBookingsUser_idx` (`idUser`),
  ADD KEY `fkBookingsRoom_idx` (`idRoom`),
  ADD KEY `fkBookingsMenu_idx` (`idMenu`);

--
-- Indici per le tabelle `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`idMenu`);

--
-- Indici per le tabelle `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`idRoom`),
  ADD UNIQUE KEY `roomNumber_UNIQUE` (`roomNumber`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `bookings`
--
ALTER TABLE `bookings`
  MODIFY `idBookings` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `menus`
--
ALTER TABLE `menus`
  MODIFY `idMenu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `rooms`
--
ALTER TABLE `rooms`
  MODIFY `idRoom` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fkBookingsMenu` FOREIGN KEY (`idMenu`) REFERENCES `menus` (`idMenu`),
  ADD CONSTRAINT `fkBookingsRoom` FOREIGN KEY (`idRoom`) REFERENCES `rooms` (`idRoom`),
  ADD CONSTRAINT `fkBookingsUser` FOREIGN KEY (`idUser`) REFERENCES `users` (`idUser`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
