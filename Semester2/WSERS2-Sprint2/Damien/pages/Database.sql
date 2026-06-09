drop database Daxda083;
create database Daxda083;
use Daxda083;


create table Clients(
    username varchar(255) primary key,
    clientPassword varchar(255),
    Email varchar(255) ,
    Phone varchar(20),
    Usertype varchar(20) DEFAULT "user"
);

create table Products(
    ProductNameEN varchar(255) primary key,
    ImageLink varchar(255),
    Price varchar(20),
    DescriptionEN text,
    DescriptionDE text
);
create table translation(
    transKey varchar(255) primary key,
    Englishtext text,
    Germantext text
);

create table Messages(
    id INT primary key AUTO_INCREMENT,
    messageText varchar(255),
    username varchar(255) not null,
    Foreign key (username) references Clients (username)
);
create table Orders(
    orderid int auto_increment primary key,
    statusEN char(15),
    statusDE char(15),
    username varchar(225),
    foreign key(username) references clients(username)
);
create table Boughtitem(
    quantity int,
    orderid int,
    ProductNameEN varchar(255),
    foreign key(orderid) references orders(orderid),
    foreign key(ProductNameEN) references products(ProductNameEN)
);

insert into Clients(username, clientPassword, Email, Phone, Usertype) values
('Cool_guy','$2y$10$e/NbG71yQwYx6fRaXht43Ol7kojKgzazPf.cO5j9dJxRWyfOHkfoO','cool@gmail.com', '66345345', 'user'),
('daxda083','$2y$10$IWi10sfIESe8DrF5JC5oMui2CDjlm5o.Vl5K1IAqTM1g6Dd9oB.Ke','123@123.com', '6767676767', 'admin');


insert into Products(ProductNameEN, ImageLink, Price, DescriptionEN, DescriptionDE) values
('Glock','../images/glock.WEBP','500$','For justice','Für Gerechtigkeit'),
('Magnum','../images/magnum.jpg','700$','For power','Für Stärke'),
('Desert eagle', '../images/Desert.WEBP', '1000$', 'For domination', 'Für die Beherrschung'),
('AWP', '../images/AWM.jpg','1000$', 'for precision','Für Präzision'),
('FATMAN','../images/FATMAN.JPG','50000000$','For total domination','Für totale Herrschaft'),
('AUG','../images/AUG.webp','12000$','For accuracy','Für Genauigkeit'),
('Scar','../images/scar.webp','7000$','For reliability','Für Zuverlässigkeit'),
('Tectical 22LR','../images/Tectical 22LR.webp','5000$','For beginners','Für Anfänger'),
('Ak47','../images/AK47.jpg','3000$','For versatility','Für Vielseitigkeit'),
('Remington870','../images/Remington870.jfif','1500$','For close combat','Für den Nahkampf'),
('Minigun','../images/minigun.webp','25000$','For overwhelming firepower','Für überwältigende Feuerkraft'),
('!!Limited Edition!!<br>WALTHER P22','../images/PINK.webp','2300$','Lets go girls!','Aufgehts Mädels!');



insert into translation(transKey, Englishtext, Germantext) values
('HomeBtn', 'Home', 'Startseite'),
('ProductsBtn', 'Products', 'Produkte'),
('RegisterBtn', 'Register', 'Registrierung'),
('LOGINBtn', 'LOGIN', 'Anmelden'),
('Welcomelable', 'Welcome to our FreedomShop <br> BUY YOUR FREEDOM BACK!', 'Willkommen auf unserem Freiheits Shop <br> KAUFEN SIE SICH IHRE FREIHEIT ZURÜCK!'),
('Passwordlable', 'Please enter your name below to continue: ', 'Bitte geben sie ihren namen hie runtern ein:'),
('Userlable', 'Pick a Username:', 'Geben sie einen Benutzername ein:'),
('1passwordlable', 'Pick a Password:', 'Geben sie ein Password ein:'),
('2passwordlable', 'Re-enter Password:', 'Geben sie das Password nachmal ein:'),
('registerlable', 'Register', 'Registriren'),
('PasswordUserErrorlable', 'Passwords match and you have chosen a valid user. You will be registered ...', 'Die Passwörter stimmen überein und Sie haben einen gültigen Benutzer ausgewählt. Sie werden registriert ...'),
('PasswordErrorlable', 'Passwords do not match or the User already exists. Please try again.', 'Die Passwörter stimmen nicht überein oder der Benutzer existiert bereits. Bitte versuchen Sie es erneut.'),
('Progresslable', 'Registration in progress...<br>', 'Registrierung läuft...<br>'),
('EmailLable', ' Enter your email:', ' Geben sie ihre email ein:'),
('PhoneLable', ' Enter your phone number:', ' Geben sie ihre Telefon nummer ein:'),
('Productslable', 'Our Products', 'Unsere Produkte'),
('succeslable', 'You logged in successfully!', 'Du hast dich erfolgreich eingeloggt!'),
('invalidlable', 'Invalid username or password.', 'Ungültiger Benutzername oder Passwort.'),
('LOGOUTBTN', 'LOGOUT', 'AUSLOGGEN'),
('QUANTITYLABLE', 'QUANTITY', 'ANZAHL'),
('CartBTN', 'Cart', 'Einkaufswagen'),
('CartLable','Shop Cart Contents','Einkaufswagen Inhalt'),
('ItemLable','Item','Gegenstand'),
('ForumLable', 'Welcome to our forum messaging space', 'Wilkommen auf unserem Forum Bereich'),
('MessageLable', 'Enter your message here:', 'Geben sie hier ihre Nachricht ein:'),
('SendBtn', 'Send', 'Senden'),
('BuyBTN', 'Buy', 'Kaufen'),
('PriceID', 'Price', 'Preis'),
('ForumMessage', 'wrote', 'schreibt'),
('DeleteBtn', 'Delete', 'Löschen'),
('OrderID', 'Order ID', 'Bestellnummer'),
('StatusID', 'Status', 'Status'),
('OrderHistoryLable', 'Order History', 'Bestellverlauf'),
('ContentsLable', 'Contents', 'Inhalt'),
('Client', 'Client', 'Kunde'),
('ProductNameID', 'Product Name', 'Produkt Name'),
('ImageLinkID', 'Image Link', 'Bild Link'),
('ENDescriptionID', 'Description in English', 'Beschreibung auf Englisch'),
('DEDescriptionID', 'Description in German', 'Beschreibung auf Deutsch'),
('CreateBtn', 'Create', 'Erstellen'),
('CheckoutBtn', 'Checkout', 'Zur Kasse');


