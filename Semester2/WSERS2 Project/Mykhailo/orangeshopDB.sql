
CREATE OR REPLACE DATABASE OrangeShopDB;
USE OrangeShopDB;


CREATE TABLE Translations (
    translationId INT PRIMARY KEY AUTO_INCREMENT,
    translationKey VARCHAR(50),
    en TEXT,
    fr TEXT,
    de TEXT
);

INSERT INTO Translations (translationKey, en, fr, de) VALUES
('Home', 'Home', 'Accueil', 'Startseite'),
('Products', 'Products', 'Produits', 'Produkte'),
('Contact', 'Contact', 'Contact', 'Kontakt'),
('About', 'About', 'À propos', 'Über'),
('Registration', 'Registration', 'Inscription', 'Registrierung'),
('Login', 'Login', 'Login', 'Login'),
('welcomelab', 'Welcome to my shop', 'Bienvenue dans ma boutique', 'Willkommen in meinem Shop'),
('name', 'Your user name', 'Votre nom d''utilisateur', 'Ihr Benutzername'),
('email', 'Your email', 'Votre adresse e-mail', 'Ihre E-Mail-Adresse'),
('reg', 'Registration form will go here', 'Le formulaire de réinscription sera disponible ici', 'Das Formular zur erneuten Registrierung wird hier eingefügt'),
('regbutton', 'Register', 'S''inscrire', 'Registrieren'),
('pasw', 'Your password', 'Votre mot de passe', 'Ihr Passwort'),
('conf', 'Confirm your password', 'Confirmez votre mot de passe', 'Bestätigen Sie Ihr Passwort'),
('secret', 'Your secret password', 'Votre mot de passe secret', 'Ihr geheimes Passwort'),
('confsecretp', 'Confirm your secret password', 'Confirmez votre mot de passe secret', 'Bestätigen Sie Ihr geheimes Passwort'),
('PasswordConfirmed', 'Password confirmed', 'Mot de passe confirmé', 'Passwort bestätigt'),
('PasswordsNoMatch', 'Passwords do not match', 'Les mots de passe ne correspondent pas', 'Passwörter stimmen nicht überein'),
('SecretConfirmed', 'Secret password confirmed', 'Mot de passe secret confirmé', 'Geheimes Passwort bestätigt'),
('SecretNoMatch', 'Secret passwords do not match', 'Les mots de passe secrets ne correspondent pas', 'Geheime Passwörter stimmen nicht überein'),
('registrationSuccess', 'Registration successful for user', 'Inscription réussie pour l''utilisateur', 'Registrierung erfolgreich für Benutzer'),
('UserExists', 'Username already exists', 'Le nom d''utilisateur existe déjà', 'Benutzername existiert bereits'),
('Welcome', 'Welcome to OrangeShop', 'Bienvenue sur OrangeShop', 'Willkommen bei OrangeShop'),
('Quality', 'Quality products delivered to your door.', 'Des produits de qualité livrés à votre porte.', 'Qualitätsprodukte direkt zu Ihnen geliefert'),
('ShopNow', 'Shop Now', 'Acheter maintenant', 'Jetzt einkaufen'),
('FeaturedProducts', 'Featured Products', 'Produits vedettes', 'Empfohlene Produkte'),
('Price', 'Price', 'Prix', 'Preis'),
('AddToCart', 'Add to Cart', 'Ajouter au panier', 'In den Warenkorb'),
('Rights', 'All rights reserved.', 'Tous droits réservés.', 'Alle Rechte vorbehalten'),
('ContactUs', 'Contact Us', 'Contactez-nous', 'Kontaktieren Sie uns'),
('OurInfo', 'Our Contact Information', 'Nos coordonnées', 'Unsere Kontaktinformationen'),
('Address', 'Address', 'Adresse', 'Adresse'),
('Phone', 'Phone', 'Téléphone', 'Telefon'),
('Email', 'Email', 'E-mail', 'E-Mail'),
('YourName', 'Your Name', 'Votre nom', 'Ihr Name'),
('YourEmail', 'Your Email', 'Votre e-mail', 'Ihre E-Mail'),
('Message', 'Message', 'Message', 'Nachricht'),
('Send', 'Send Message', 'Envoyer le message', 'Nachricht senden'),
('Thanks', 'Thank you for contacting us!', 'Merci de nous avoir contactés !', 'Danke, dass Sie uns kontaktiert haben!'),
('PaswConf', 'Password registration confirm', 'Confirmation de l''enregistrement du mot de passe', 'Passwortregistrierung bestätigen');


INSERT INTO Translations (translationKey, en, fr, de) VALUES
('Forum', 'Forum', 'Forum', 'Forum'),
('NewThread', 'Create New Thread', 'Créer un nouveau sujet', 'Neues Thema erstellen'),
('ThreadTitle', 'Thread title', 'Titre du sujet', 'Thementitel'),
('ThreadContent', 'Message', 'Message', 'Nachricht'),
('PostReply', 'Post Reply', 'Répondre', 'Antworten'),
('NoThreads', 'No threads yet. Be the first to post!', 'Aucun sujet pour le moment. Soyez le premier à poster !', 'Noch keine Themen. Sei der Erste, der postet!');


CREATE TABLE Products (
    productId INT PRIMARY KEY AUTO_INCREMENT,
    productName VARCHAR(50),
    productPicture VARCHAR(100),
    price DECIMAL(10, 2),
    description TEXT
);

INSERT INTO Products (productName, productPicture, price, description) VALUES
('Mandarin', 'pictures/mandarin.webp', 29.99, 'Fresh and sweet mandarin oranges.'),
('Orange', 'pictures/orange.webp', 39.99, 'Juicy, vitamin-rich oranges.'),
('Red Orange', 'pictures/red-orange.webp', 49.99, 'Unique red-flesh oranges with a tangy flavor.');


CREATE TABLE Clients (
    clientId INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) unique,
    email VARCHAR(100),
    password VARCHAR(255),
    secretPassword VARCHAR(255),
    adminStatus VARCHAR(20)
);

INSERT INTO Clients (username, email, password, secretPassword, adminStatus) VALUES
('Dude', 'dude@dude.com', '$2y$10$uZGcErlHfhHAtxzh/SIQVuw9pBLFw9t92LH6cMMoZgR6P2SSv.oYy', '$2y$10$v6YlpM6RavWP11gRSrB7veCSTGYckC/42gv170zDpOxuGxXZaB8QS', '1'),
('toto', 'toto2@gmail.com', '$2y$10$VvjgPClEgSwjo89WmvanpelOwU0cLmfaDVhZu6qW8o55XWXdXxiRC', '$2y$10$CAzeT38a/ngMUSh83r1ocOmYKEjXSWsT.y9ZHsE.czR2x14P4Ctfe', '0'),
('lolo', 'lolo@gmail.com', '$2y$10$jdzUXrlRVvTYl22BoORqfewYpRlXRT/hbozAzmxlFtPh6y.sRHNui', '$2y$10$..D.CuFCil8XZbrOePNyjOl81XFleEK1OoEGqTA7dC1MWgc0oLIvy', 'regular client'),
('UNI', 'uni@gmail.com', '$2y$10$z.HhlIUVC00frjfUQ212hOLOL3khrs/iqxy9efC6iLh78GKqMQvc2', '$2y$10$F344rwAsjMVgNMiVZw3U1OYuAS3xqCg2OKKFz3Dgw6awYHHIGo3N6', '1');

create table Messages(
    id INT PRIMARY KEY AUTO_INCREMENT,
    messageText varchar(255),
    username  VARCHAR(50) not null,
    FOREIGN KEY (username) REFERENCES Clients (username)
);

-- Additional translation keys added to support more UI text used in the PHP code
INSERT INTO Translations (translationKey, en, fr, de) VALUES
('Cart', 'Cart', 'Panier', 'Warenkorb'),
('cart_title', 'Shopping Cart', 'Panier', 'Warenkorb'),
('cart_empty', 'Your cart is empty.', 'Votre panier est vide.', 'Ihr Warenkorb ist leer.'),
('product', 'Product', 'Produit', 'Produkt'),
('price', 'Price', 'Prix', 'Preis'),
('qty', 'Qty', 'Qté', 'Anz.'),
('subtotal', 'Subtotal', 'Sous-total', 'Zwischensumme'),
('total', 'Total', 'Total', 'Gesamt'),
('buy', 'Buy', 'Acheter', 'Kaufen'),
('purchase_success', 'Thank you for your purchase!', 'Merci pour votre achat !', 'Danke für Ihren Kauf!'),
('ProductsLoadError', 'Could not load products.', 'Impossible de charger les produits.', 'Produkte konnten nicht geladen werden.'),
('products_title', 'OrangeShop - Products', 'OrangeShop - Produits', 'OrangeShop - Produkte'),
('site_title', 'OrangeShop', 'OrangeShop', 'OrangeShop'),
('NewMessage', 'New Message', 'Nouveau message', 'Neue Nachricht'),
('TypeYourMessage', 'Type your message here...', 'Tapez votre message ici...', 'Geben Sie hier Ihre Nachricht ein...'),
('ReturnToLogin', 'Return to Login', 'Retour à la connexion', 'Zurück zum Login'),
('LoggedOut', 'You have been logged out successfully.', 'Vous avez été déconnecté avec succès.', 'Sie wurden erfolgreich abgemeldet.'),
('AllFieldsRequired', 'All fields are required.', 'Tous les champs sont obligatoires.', 'Alle Felder sind erforderlich.'),
('PriceNumeric', 'Price must be numeric.', 'Le prix doit être numérique.', 'Preis muss numerisch sein.'),
('ImageUploadFailed', 'Image upload failed.', "Échec du téléversement de l'image.", 'Bild-Upload fehlgeschlagen.'),
('InvalidImage', 'Uploaded file is not a valid image.', 'Le fichier téléchargé n\'est pas une image valide.', 'Die hochgeladene Datei ist kein gültiges Bild.'),
('InvalidImageType', 'Only JPG, PNG or WEBP images allowed.', 'Seuls JPG, PNG ou WEBP sont autorisés.', 'Nur JPG, PNG oder WEBP sind erlaubt.'),
('CouldNotSaveImage', 'Could not save image.', 'Impossible d\'enregistrer l\'image.', 'Bild konnte nicht gespeichert werden.'),
('ProductAdded', 'Product successfully added.', 'Produit ajouté avec succès.', 'Produkt erfolgreich hinzugefügt.'),
('DatabaseInsertFailed', 'Database insert failed: ', 'Échec de l\'insertion en base : ', 'Datenbankeinfügung fehlgeschlagen: '),
('DatabasePrepareFailed', 'Database error: could not prepare statement.', 'Erreur de base de données : impossible de préparer la requête.', 'Datenbankfehler: Statement konnte nicht vorbereitet werden.'),
('AdminPanel', 'Admin Panel', 'Panneau d\'administration', 'Admin-Bereich'),
('CreateProduct', 'Create Product', 'Créer un produit', 'Produkt erstellen'),
('ProductName', 'Product name', 'Nom du produit', 'Produktname'),
('Description', 'Description', 'Description', 'Beschreibung'),
('AddProduct', 'Add Product', 'Ajouter le produit', 'Produkt hinzufügen'),
('AdminFooter', '© OrangeShop — Admin Panel', '© OrangeShop — Panneau d\'administration', '© OrangeShop — Admin-Bereich');

