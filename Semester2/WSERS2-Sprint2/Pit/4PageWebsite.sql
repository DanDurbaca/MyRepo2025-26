-- phpMyAdmin SQL Dump

DROP DATABASE IF EXISTS 4pagewebsite;
CREATE DATABASE 4pagewebsite;
USE 4pagewebsite;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

-- Table: Clients

CREATE TABLE Clients (
  Username VARCHAR(100) NOT NULL,
  UserPassword VARCHAR(255) DEFAULT NULL,
  Email VARCHAR(100) DEFAULT NULL,
  Firstname VARCHAR(50) DEFAULT NULL,
  UserType VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY (Username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Clients (Username, UserPassword, Email, Firstname, UserType) VALUES
('Admin', '$2y$10$Hy6iZOFHmRWvK8X8aHIKHeH/2dM46m/a9XFuDnZxon6v7u0bLOmR6', 'admin@school.lu', 'Admin', 'admin'),
('Pit', '$2y$10$dqITXyichRtBbDpkaCfDSeRDEmhoRDDY1uyPUcqLBGWqevFM1iWhG', 'gfhfghf@school.lu', 'Pit', 'regular'),
('PitAdmin', '$2y$10$/cvUtpZCDAc5iXScZM3t7.AcBWU0DF4voEsi35nVth5k79DJqm4jq', 'pit@gmail.com', 'Pit', 'admin'),
('TestA', '$2y$10$pnJkVv5i6Ub86/dLXXf29eR0hnJqEqAEu95on/Lu9KaVYptpDmJEy', 'krasspit@gmail.com', 'Pit', 'admin');

-- Table: messages

CREATE TABLE messages (
  id INT(11) NOT NULL AUTO_INCREMENT,
  messageText VARCHAR(255) DEFAULT NULL,
  Username VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  KEY Username (Username),
  CONSTRAINT messages_ibfk_1
    FOREIGN KEY (Username)
    REFERENCES Clients (Username)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO messages (id, messageText, Username) VALUES
(2, 'Forum Test', 'PitAdmin'),
(3, 'Forum Test', 'PitAdmin'),
(4, 'Forum Test New', 'PitAdmin'),
(5, 'New Test Account', 'Pit'),
(6, 'Double Text Test completed', 'Pit'),
(7, 'Attention Clients! New products are available in the shop.', 'PitAdmin');

-- Table: Products

CREATE TABLE Products (
  ProductNameEN VARCHAR(100) NOT NULL,
  ImageLink VARCHAR(255) DEFAULT NULL,
  Price DECIMAL(10,2) DEFAULT NULL,
  DescriptionEN VARCHAR(255) DEFAULT NULL,
  DescriptionDE VARCHAR(255) DEFAULT NULL,
  ProductNameDE VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (ProductNameEN)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`ProductNameEN`, `ImageLink`, `Price`, `DescriptionEN`, `DescriptionDE`, `ProductNameDE`) VALUES
('Ecstasy', 'pills.jpg', '15.00', 'The best ecstasy you can get!', 'Das beste Ecstasy was ihr bekommen könnt!', 'Ecstasy'),
('Fentanyl', 'weis.jpg', '40.00', 'The best fentanyl you can get!', 'Das beste Fentanyl was ihr bekommen könnt!', 'Fentanyl'),
('LSD', 'lsd_1765285321.webp', '12.00', 'The best LSD you can get!', 'Das beste LSD was ihr bekommen könnt!', 'LSD'),
('Magic Mushrooms', 'magic_mushrooms_1776159674.jpg', '12.00', 'Fresh mushrooms for a special trip!', 'Frische Pilze für spezielle Trips!', 'Magische Pilze'),
('Weed', 'gras.jpg', '10.00', 'The best weed you can get!', 'Das beste Graß was ihr bekommen könnt!', 'Graß');
-- Table: Translation

CREATE TABLE Translation (
  KeyWord VARCHAR(255) NOT NULL,
  EnglishText VARCHAR(255) DEFAULT NULL,
  DeutschText VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (KeyWord)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Translation (KeyWord, EnglishText, DeutschText) VALUES
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
('CheckoutBtn', 'Make Order', 'Bestellung aufgeben'),
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
('HomeTextH3S', 'We are a team of students, who are doing this as a school project.', 'Wir sind ein Team von Schülern, die dies als Schulprojekt machen.'),
('HomeTextH3T', 'I hope you enjoy our website and we hope to see you soon again!', 'Ich hoffe, Ihnen gefällt unsere Website und wir hoffen, Sie bald wiederzusehen!'),
('HomeTextTitle', 'Welcome to our webshop!', 'Willkommen in unserem Webshop!'),
('LogoutBtn', 'LOGOUT', 'ABMELDEN'),
('ProductsBtn', 'Products', 'Produkte'),
('ProductsQuantity', 'for', 'für'),
('ProductsSelect', 'Choose your quantity', 'Wähle deine Menge'),
('ProductsSubTitle', 'Fair prices! Fairtrade! Free shipping!', 'Faire Preise! Fairtrade! Gratis Versand!'),
('ProductsTitle', 'Our high quality products!', 'Unsere Produkte aus Top Qualität!'),
('ProfileBtn', 'Profile', 'Profil'),
('ProfileError', 'Password mismatch!', 'Passwort stimmt nicht überein!'),
('ProfileLogin', 'Login', 'Einloggen'),
('ProfileLoginOk', 'You are logged in', 'Sie sind jetzt eingeloggt'),
('ProfilePassword', 'Your password', 'Passwort'),
('ProfileUsername', 'Username', 'Nutzername'),
('ProfileUserNotExist', 'Login not possible! Register first', 'Login nicht möglich! Registriere dich bitte zuerst'),
('RegButton', 'Register', 'Registrieren'),
('RegEmail', 'Your email address', 'E-Mail'),
('RegFirstName', 'Your first name', 'Dein Name'),
('RegisterBtn', 'Register', 'Registrieren'),
('RegMatch', 'Passwords match. Registration in progress.', 'Passwörter stimmen. Registrierung erfolgt.'),
('RegNoMatch', 'Password mismatch or user already exists', 'Passwörter stimmen nicht überein oder Nutzer existiert bereits.'),
('RegPassword', 'Pick a password', 'Wähle ein Passwort'),
('RegPasswordConfirm', 'Confirm your password', 'Bestätige dein Passwort'),
('RegTitle', 'Registration form', 'Registrierungsformular'),
('RegUsername', 'Pick a username', 'Wähle einen Nutzernamen'),

('AdminNameEN', 'Product name (EN):', 'Produktname (EN):'),
('AdminNameDE', 'Product name (DE):', 'Produktname (DE):'),
('AdminPrice', 'Price (EUR per g):', 'Preis (EUR pro g):'),
('AdminDescEN', 'Description (EN):', 'Beschreibung (EN):'),
('AdminDescDE', 'Description (DE):', 'Beschreibung (DE):'),
('AdminImage', 'Product image:', 'Produktbild:'),
('AdminMsgFillFields', 'Please fill in all fields and use a numeric price.', 'Bitte alle Felder ausfüllen und einen numerischen Preis verwenden.'),
('AdminMsgSelectImage', 'Please select an image to upload.', 'Bitte ein Bild auswählen.'),
('AdminMsgFileLarge', 'File too large. Max 5MB.', 'Datei zu groß. Maximal 5MB.'),
('AdminMsgInvalidType', 'Invalid file type. Only PNG / JPG / WEBP allowed.', 'Ungültiger Dateityp. Nur PNG / JPG / WEBP erlaubt.'),
('AdminMsgCreated', 'Product created successfully.', 'Produkt erfolgreich erstellt.'),
('AdminMsgDbError', 'Database error:', 'Datenbankfehler:'),
('AdminMsgSaveError', 'Error saving uploaded file.', 'Fehler beim Speichern der Datei.'),
('AdminCreateBtn', 'Create product', 'Produkt erstellen'),
('ForumDeleteBtn', 'Delete', 'Löschen'),

('ContactSubTitle', 'Send us a message', 'Sende uns eine Nachricht'),
('ContactFillFields', 'Please fill in all fields.', 'Bitte fülle alle Felder aus.'),
('ContactInvalidEmail', 'Please enter a valid email address.', 'Bitte gib eine gültige E-Mail-Adresse ein.'),
('ContactSuccess', 'Thank you! Your message has been received.', 'Danke! Deine Nachricht wurde empfangen.'),
('ContactFormTitle', 'Contact Form', 'Kontaktformular'),
('ContactName', 'Name', 'Name'),
('ContactEmail', 'Email', 'E-Mail'),
('ContactSubject', 'Subject', 'Betreff'),
('ContactMessage', 'Message', 'Nachricht'),
('ContactSendBtn', 'Send message', 'Nachricht senden'),

('OrderCreated', 'Your order was placed successfully. Status: pending.', 'Deine Bestellung wurde erfolgreich aufgegeben. Status: ausstehend.'),
('OrderCreateError', 'Error creating order.', 'Fehler beim Erstellen der Bestellung.'),
('OrderHistory', 'Order History', 'Bestellverlauf'),
('OrderNoHistory', 'You have no previous orders.', 'Du hast noch keine Bestellungen.'),
('OrderNumber', 'Order', 'Bestellung'),
('OrderDate', 'Date', 'Datum'),
('OrderTotal', 'Total', 'Gesamt'),
('OrderStatus', 'Status', 'Status'),
('OrderBy', 'by', 'von'),

('AdminOrderUpdated', 'Order status updated.', 'Bestellstatus wurde aktualisiert.'),
('AdminOrderUpdateError', 'Error updating order status.', 'Fehler beim Aktualisieren des Bestellstatus.'),
('AdminCustomerOrders', 'Customer Orders', 'Kundenbestellungen'),
('AdminNoOrders', 'No orders yet.', 'Noch keine Bestellungen.'),
('AdminCurrentStatus', 'Current status', 'Aktueller Status'),
('AdminUpdateStatus', 'Update status', 'Status aktualisieren'),

('StatusPending', 'Pending', 'Ausstehend'),
('StatusAllowed', 'Allowed', 'Erlaubt'),
('StatusRejected', 'Rejected', 'Abgelehnt'),
('StatusCompleted', 'Completed', 'Abgeschlossen');

-- Table: Orders
-- One user can have many orders

CREATE TABLE Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(100) NOT NULL,
    TotalPrice DECIMAL(10,2) NOT NULL,
    OrderStatus ENUM('pending', 'allowed', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    OrderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_clients
        FOREIGN KEY (Username)
        REFERENCES Clients(Username)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: OrderItems
-- One order can have many products

CREATE TABLE OrderItems (
    OrderItemID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    ProductNameEN VARCHAR(100) NOT NULL,
    ProductNameDE VARCHAR(100) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Quantity INT NOT NULL,
    Subtotal DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_orderitems_orders
        FOREIGN KEY (OrderID)
        REFERENCES Orders(OrderID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_orderitems_products
        FOREIGN KEY (ProductNameEN)
        REFERENCES Products(ProductNameEN)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;