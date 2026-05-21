-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 10:24 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6
DROP Database 4pagewebsite;
CREATE Database 4pagewebsite;
USE 4pagewebsite;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `4pagewebsite`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `Username` varchar(100) NOT NULL,
  `UserPassword` varchar(255) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Firstname` varchar(50) DEFAULT NULL,
  `UserType` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`Username`, `UserPassword`, `Email`, `Firstname`, `UserType`) VALUES
('Admin', '$2y$10$Hy6iZOFHmRWvK8X8aHIKHeH/2dM46m/a9XFuDnZxon6v7u0bLOmR6', 'admin@school.lu', 'Admin', 'admin'),
('Pit', '$2y$10$dqITXyichRtBbDpkaCfDSeRDEmhoRDDY1uyPUcqLBGWqevFM1iWhG', 'gfhfghf@school.lu', 'Pit', 'regular'),
('PitAdmin', '$2y$10$/cvUtpZCDAc5iXScZM3t7.AcBWU0DF4voEsi35nVth5k79DJqm4jq', 'pit@gmail.com', 'Pit', 'admin'),
('TestA', '$2y$10$pnJkVv5i6Ub86/dLXXf29eR0hnJqEqAEu95on/Lu9KaVYptpDmJEy', 'krasspit@gmail.com', 'Pit', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `messageText` varchar(255) DEFAULT NULL,
  `Username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `messageText`, `Username`) VALUES
(2, 'Forum Test', 'PitAdmin'),
(3, 'Forum Test', 'PitAdmin'),
(4, 'Forum Test New', 'PitAdmin'),
(5, 'New Test Account', 'Pit'),
(6, 'Double Text Test completed', 'Pit'),
(7, 'Attention Clients!!! Our new fresh Mushrooms just dropped!!!!', 'PitAdmin');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductNameEN` varchar(100) NOT NULL,
  `ImageLink` varchar(255) DEFAULT NULL,
  `Price` varchar(10) DEFAULT NULL,
  `DescriptionEN` varchar(255) DEFAULT NULL,
  `DescriptionDE` varchar(255) DEFAULT NULL,
  `ProductNameDE` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductNameEN`, `ImageLink`, `Price`, `DescriptionEN`, `DescriptionDE`, `ProductNameDE`) VALUES
('Ecstasy', 'pills.jpg', '15$', 'The best ecstasy you can get!', 'Das beste Ecstasy was ihr bekommen könnt!', 'Ecstasy'),
('Fentanyl', 'weis.jpg', '40$', 'The best fentanyl you can get!', 'Das beste Fentanyl was ihr bekommen könnt!', 'Fentanyl'),
('LSD', 'lsd_1765285321.webp', '12', 'The best LSD you can get!', 'Das beste LSD was ihr bekommen könnt!', 'LSD'),
('Magic Mushrooms', 'magic_mushrooms_1776159674.jpg', '12', 'Fresh mushrooms for a special trip!', 'Frische Pilze für spezielle Trips!', 'Magische Pilze'),
('Weed', 'gras.jpg', '10$', 'The best weed you can get!', 'Das beste Graß was ihr bekommen könnt!', 'Graß');

-- --------------------------------------------------------

--
-- Table structure for table `translation`
--

CREATE TABLE `translation` (
  `KeyWord` varchar(255) NOT NULL,
  `EnglishText` varchar(255) DEFAULT NULL,
  `DeutschText` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `translation`
--

INSERT INTO `translation` (`KeyWord`, `EnglishText`, `DeutschText`) VALUES
('AddToCartBtn', 'Add to cart', 'In den Warenkorb'),
('AdminBtn', 'Admin page', 'Admin-Seite'),
('AdminCreateProduct', 'Create a new product', 'Neues Produkt erstellen'),
('AdminSubTitle', 'Add new products for the shop', 'Neue Produkte für den Shop hinzufügen'),
('AdminTitle', 'Admin – Create Product', 'Admin – Produkt erstellen'),
('CartAction', 'Action', 'Aktion'),
('CartBtn', 'Cart', 'Warenkorb'),
('CartClear', 'Clear cart', 'Warenkorb leeren'),
('CartEmpty', 'Your cart is empty.', 'Dein Warenkorb ist leer.'),
('CartPrice', 'Price', 'Preis'),
('CartProduct', 'Product', 'Produkt'),
('CartQuantity', 'Quantity', 'Menge'),
('CartRemove', 'Remove', 'Entfernen'),
('CartSubTitle', 'All selected products are listed below.', 'Alle ausgewählten Produkte sind unten aufgelistet.'),
('CartSubtotal', 'Subtotal', 'Zwischensumme'),
('CartTitle', 'Your Cart', 'Dein Warenkorb'),
('CartTotal', 'Total', 'Gesamt'),
('CheckoutBtn', 'Checkout', 'Zur Kasse'),
('ContactBtn', 'Contact', 'Kontakt'),
('ContactText', 'Contact us:', 'Kontaktiert uns:'),
('ForumBtn', 'Forum', 'Forum'),
('ForumPlaceholder', 'Type a new message', 'Neue Nachricht schreiben'),
('ForumSendBtn', 'Send Message', 'Nachricht senden'),
('ForumTitle', 'Welcome to our forum messaging space', 'Willkommen im Forum'),
('ForumUser', 'User', 'Benutzer'),
('ForumWrote', 'wrote', 'schrieb'),
('HomeBtn', 'Home', 'Hauptmenü'),
('HomeTextH2', 'Your Number 1 worldwide!', 'Ihre Nummer 1 weltweit!'),
('HomeTextH3F', 'Welcome to our Website!', 'Willkommen auf unserer Website!'),
('HomeTextH3S', 'We are a team of students, who are doing this as a school project xD.', 'Wir sind ein Team von Schülern, die dies als Schulprojekt machen xD.'),
('HomeTextH3T', 'I hope you enjoy our website and we hope to see you soon again!', 'Ich hoffe, Ihnen gefällt unsere Website und wir hoffen, Sie bald wiederzusehen!'),
('HomeTextTitle', 'Buy your drugs here!', 'Kaufen Sie Ihre Medikamente hier!'),
('LogoutBtn', 'LOGOUT', 'ABMELDEN'),
('ProductsBtn', 'Products', 'Produkte'),
('ProductsQuantity', 'for', 'für'),
('ProductsSelect', 'Choose your quantity', 'Wähle deine Menge'),
('ProductsSubTitle', 'Fair Prices! Fairtrade! Free shipping!', 'Fairer Preis! Fairtrade! Gratis Versand!'),
('ProductsTitle', 'Our high quality products!', 'Unsere Produkte aus Top Qualität!'),
('ProfileBtn', 'Profile', 'Profil'),
('ProfileError', 'Password mismatch!', 'Password stimmt nicht überein!'),
('ProfileLogin', 'Login', 'Einloggen'),
('ProfileLoginOk', 'You are logged in', 'Sie sind jetzt eingeloggt'),
('ProfilePassword', 'Your password', 'Passwort'),
('ProfileUsername', 'Username', 'Nutzername'),
('ProfileUserNotExist', 'Login not possible! Register first', 'Login nicht möglich! Registriere dich bitte zu erst'),
('RegButton', 'Register', 'Registrieren'),
('RegEmail', 'Your email address', 'Email'),
('RegFirstName', 'Your first name', 'Dein Name'),
('RegisterBtn', 'Register', 'Registrieren'),
('RegMatch', 'Passwords match. Registration in progress.', 'Passwörter stimmen. Registrierung erfolgt.'),
('RegNoMatch', 'Password mismatch or user already exists', 'Passwörter stimmen nicht überein oder Nutzer existiert bereits.'),
('RegPassword', 'Pick a password', 'Wähle ein Passwort'),
('RegPasswordConfirm', 'Confirm your password', 'Bestätige dein Passwort'),
('RegTitle', 'Registration form', 'Registrierungs Formular'),
('RegUsername', 'Pick a username', 'Wähle einen Nutzernamen'),
("AdminNameEN", "Product name (EN):", "Produktname (EN):"),
("AdminNameDE", "Product name (DE):", "Produktname (DE):"),
("AdminPrice", "Price (EUR per g):", "Preis (EUR pro g):"),
("AdminDescEN", "Description (EN):", "Beschreibung (EN):"),
("AdminDescDE", "Description (DE):", "Beschreibung (DE):"),
("AdminImage", "Product image:", "Produktbild:"),
("AdminMsgFillFields", "Please fill in all fields and use a numeric price.", "Bitte alle Felder ausfüllen und einen numerischen Preis verwenden."),
("AdminMsgSelectImage", "Please select an image to upload.", "Bitte ein Bild auswählen."),
("AdminMsgFileLarge", "File too large. Max 5MB.", "Datei zu groß. Maximal 5MB."),
("AdminMsgInvalidType", "Invalid file type. Only PNG / JPG / WEBP allowed.", "Ungültiger Dateityp. Nur PNG / JPG / WEBP erlaubt."),
("AdminMsgCreated", "Product created successfully.", "Produkt erfolgreich erstellt."),
("AdminMsgDbError", "Database error:", "Datenbankfehler:"),
("AdminMsgSaveError", "Error saving uploaded file.", "Fehler beim Speichern der Datei."),
("AdminCreateBtn", "Create product", "Produkt erstellen"),
('ForumDeleteBtn', 'Delete', 'Löschen');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`Username`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Username` (`Username`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductNameEN`);

--
-- Indexes for table `translation`
--
ALTER TABLE `translation`
  ADD PRIMARY KEY (`KeyWord`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`Username`) REFERENCES `clients` (`Username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
