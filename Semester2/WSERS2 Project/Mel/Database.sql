drop database DatabasePouMe708;
create database DatabasePouMe708;
use DatabasePouMe708;

create table Client(
    Username varchar(20) primary key,
    HashedPassword varchar(200),
    email varchar(20),
    country varchar(20),
    websiteRole varchar(6)
);

create table SupportMessage(
    SupportName varchar(20),
    Email varchar(20),
    SupportMessage varchar(300)
);

create table Products(
    ProductNameEN   varchar(100)    NOT NULL,
    ProductNameGE   varchar(100)    NOT NULL,
    ImageLink       varchar(100)    NOT NULL,
    Price           int,
    DescriptionEN   varchar(100)    NOT NULL,
    DescriptionGE   varchar(100)    NOT NULL,
    id              Integer         Primary key Auto_Increment
);

create table Translation(
    TranslationKey varchar(20),
    EnglishText varchar(1000),
    GermanText varchar(1000)
);

create table Messages(
    id INT Auto_Increment Primary key,
    messageText varchar(255),
    username varchar(20),
    foreign key(username) references Client(username)
    
);

insert into Client(Username,HashedPassword,email,country,websiteRole) VALUES
('Dude','$2y$10$oLGmqO1LjPXAcM6v/cE8fuvqv8i0iy/VYmUo9cgKQoGydyb/sN1Ly','dude@dude','Ro','user'),
('Mel','$2y$10$C5LYvrPEdHyMZz515QKNLewKGxglwmpa2KbJCVcQFll0uUSUUHvTe','a@a','lu','admin');

insert into SupportMessage(SupportName,Email,SupportMessage) VALUES
('frfr','fra@gmail.com','fwsf');

insert into Products(ProductNameEN,ProductNameGE,ImageLink,Price,DescriptionEN,DescriptionGE) VALUES
('Operation Riptide Case','Operation Riptide Case','OperationRiptideCase.webp',29.99,'Dive into the Riptide collection with rare skins inspired by the ocean’s power.','Tauche ein in die Riptide-Kollektion mit seltenen Skins, die von der Kraft des Ozeans inspiriert sind.'),
('Revolution Case','Revolution Case','RevolutionCase.webp',9.99,'Unleash bold designs and vibrant colors with this revolutionary CS:GO case.','Entfessle mutige Designs und lebendige Farben mit dieser revolutionären CS:GO-Kiste.'),
('Recoil Case','Recoil Case','RecoilCase.webp',7.99,'Packed with skins that echo the intensity of recoil and raw firepower.','Gefüllt mit Skins, die die Intensität von Rückstoß und purer Feuerkraft widerspiegeln.'),
('Danger Zone Case','Danger Zone Case','DangerZoneCase.webp',7.99,'Step into the Danger Zone with exclusive skins designed for high-risk players.','Betritt die Danger Zone mit exklusiven Skins, die für risikofreudige Spieler entworfen wurden.'),
('Dreams & Nightmares Case','Dreams & Nightmares Case','Dreams&NightmaresCase.webp',4.99,'A surreal mix of fantasy and horror skins that blur the line between dream and reality.','Eine surreale Mischung aus Fantasie- und Horror-Skins, die die Grenze zwischen Traum und Realität verschwimmen lassen.'),
('Sealed Genesis Terminal','Sealed Genesis Terminal','SealedGenesisTerminal.webp',0.99,'Marking the dawn of a new era, this sealed case hides mysterious treasures.','Diese versiegelte Kiste markiert den Beginn einer neuen Ära und verbirgt geheimnisvolle Schätze.'),
('CS:GO Weapon Case','CS:GO Weapon Case','CSGOWeaponCase.webp',4.99,'Classic CS:GO skins blending fantasy and dark themes for collectors.','Klassische CS:GO-Skins, die Fantasie und düstere Themen für Sammler vereinen.'),
('CS:GO Weapon Case 2','CS:GO Weapon Case 2','CSGOWeaponCase2.webp',9.99,'A collection of daring weapon designs for players who thrive on bold style.','Eine Sammlung gewagter Waffendesigns für Spieler, die auf mutigen Stil setzen.'),
('CS:GO Weapon Case 3','CS:GO Weapon Case 3','CSGOWeaponCase3.webp',10.99,'Ignite your arsenal with electrifying weapon skins full of energy and spark.','Entfache dein Arsenal mit elektrisierenden Waffenskins voller Energie und Funken.'),
('Operation Bravo Case','Operation Bravo Case','OperationBravoCase.webp',7.99,'Bravo brings a wide range of skins with designs inspired by precision and recoil.','Bravo bietet eine große Auswahl an Skins mit Designs, die von Präzision und Rückstoß inspiriert sind.'),
('eSports 2013 Case','eSports 2013 Case','eSports2013Case.webp',99.99,'Celebrate the roots of CS:GO eSports with this iconic case of early treasures.','Feiere die Wurzeln von CS:GO eSports mit dieser ikonischen Kiste voller früher Schätze.'),
('Kilowatt Case','Kilowatt Case','KilowattCase.webp',10.99,'Charge your inventory with high-voltage skins designed to electrify your playstyle.','Lade dein Inventar mit Hochvolt-Skins auf, die deinen Spielstil elektrisieren.');

insert into Translation(TranslationKey,EnglishText,GermanText) VALUES
('HomeBtn','Home','Startseite'),
('ProductBtn','Product','Produkte'),
('AboutUSBtn','About us','Über uns'),
('ContactBtn','Contact','Kontakt'),
('RegistrationBtn','Registration','Registrieren'),
('LoginBtn','Login','Anmelden'),
('WagenBtn','Cart','Warenkorb'),
('ForumBtn','Forum','Forum'),
('LogoutBtn','LOGOUT','AUSLOGGEN'),

('WelcomeText1','Welcome to the CS:GO Case Shop!','Willkommen im CS:GO Case Shop!'),
('WelcomeText2','Enter the ultimate marketplace for CS:GO cases! At our shop, you’ll find a wide selection of cases featuring exclusive weapon skins and rare collectibles.<br>Whether you’re chasing legendary knives, vibrant rifles, or unique pistols, every case is a chance to unlock something extraordinary.<br><br>Browse our collection, imagine the possibilities, and take your inventory to the next level. You never know what rare drop awaits you!<br>Let the opening begin.','Tauche ein in den ultimativen Marktplatz für CS:GO Cases! In unserem Shop findest du eine große Auswahl an Cases mit exklusiven Waffenskins und seltenen Sammlerstücken.<br>Ob du legendäre Messer, auffällige Gewehre oder einzigartige Pistolen jagst – jede Kiste ist eine Chance, etwas Außergewöhnliches zu erhalten.<br><br>Stöbere durch unsere Kollektion, träume von den Möglichkeiten und bringe dein Inventar auf das nächste Level. Du weißt nie, welcher seltene Drop auf dich wartet!<br>Lass das Öffnen beginnen.'),

('WelcomeTextProducts','Our Products','Unsere Produkte'),
('WelcomeTextProducts2','Add to Cart','In den Warenkorb'),

('WelcomeTextaboutus','About us','Über uns'),
('WelcomeTextaboutus2','Our shop is young and passionate, driven by the excitement of CS:GO and the thrill of opening cases.','Unser Shop ist jung und leidenschaftlich, angetrieben von der Begeisterung für CS:GO und dem Nervenkitzel des Case-Openings.'),
('WelcomeTextaboutus3','We specialize in CS:GO cases carefully selected collections filled with exclusive skins and rare items that players love to unlock.','Unsere Spezialität sind CS:GO Cases sorgfältig ausgewählte Kollektionen voller exklusiver Skins und seltener Items, die Spieler gerne freischalten.'),
('WelcomeTextaboutus4','The tone is energetic and fun, highlighting the excitement, playfulness, and emotional rush of discovering rare drops inside each case.','Der Ton ist energiegeladen und spielerisch und betont die Aufregung, Verspieltheit und den emotionalen Kick beim Entdecken seltener Drops in jeder Kiste.'),

('WelcomeTextContact','Contact Us','Kontaktieren Sie uns'),
('WelcomeTextContact2','Do you have any questions? Send us a message!','Haben Sie Fragen? Senden Sie uns eine Nachricht!'),
('WelcomeTextContact3','Name:','Name:'),
('WelcomeTextContact4','Email.','Email'),
('WelcomeTextContact5','Message:','Nachricht:'),
('WelcomeTextContact6','Send','Senden'),
('WelcomeTextContact7','Thanks for you message','Danke für deine Nachricht.'),
('WelcomeTextContact8','we will aswer the message soon','Wir werden die Nachricht bald beantworten.'),

('WelcomeTextRegistor','Registrate now!','Jetzt registrieren!'),
('WelcomeTextRegistor2','Registration in process<br>','Registrierung läuft<br>'),
('WelcomeTextRegistor3','Password match. You will be registered ...','Passwort übereinstimmt. Sie werden registriert ...'),
('WelcomeTextRegistor4','Error. The two passwords do not match or the user already exists. Please try again!','Fehler. Die beiden Passwörter stimmen nicht überein oder der Benutzer existiert bereits. Bitte versuchen Sie es erneut!'),
('WelcomeTextRegistor5','Pick a <b>username:</b>','Wählen Sie einen <b>Benutzernamen:</b>'),
('WelcomeTextRegistor6','Pick your <b>Mail:</b>','Wählen Sie ihre <b>E-Mail:</b>'),
('WelcomeTextRegistor7','Pick a <b>password:</b>','Wählen Sie ein <b>Passwort:</b>'),
('WelcomeTextRegistor8','Pick your <b>country:</b>','Wählen Sie ihr <b>Land:</b>'),
('WelcomeTextRegistor9','Registrate','Registrieren'),

('WelcomeTextLogin','Login now!','Jetzt einloggen!'),
('WelcomeTextLogin2','Login successful! Welcome back,','Login erfolgreich! Willkommen zurück,'),
('WelcomeTextLogin3','Error: Username or password incorrect, or account does not exist.','Fehler: Benutzername oder Passwort falsch, oder Account existiert nicht.'),
('WelcomeTextLogin4','Username','Benutzername'),
('WelcomeTextLogin5','Password','Passwort'),
('WelcomeTextLogin6','Login','Einloggen'),

('Admin','Admin','Admin'),
('Admin1','Access denied. Admins only.','Zugriff verweigert. Nur für Administratoren.'),
('Admin2','You will be redirected...','Sie werden weitergeleitet...'),
('Admin3','Product successfully created!','Produkt erfolgreich erstellt!'),
('Admin4','Image upload failed.','Das Hochladen des Bildes ist fehlgeschlagen.'),
('Admin5','Create New Product','Neues Produkt erstellen'),
('Admin6','Product Name (EN),:','Produktname (EN),:'),
('Admin7','Product Name (GE),:','Produktname (GE),:'),
('Admin8','Product Image:','Produktbild:'),
('Admin9','Price:','Preis'),
('Admin10','Description (EN),:','Beschreibung (EN),:'),
('Admin11','Description (GE),:','Beschreibung (GE),:'),
('Admin12','Create Product','Produkt erstellen'),

('WelcomeForum1','User ','Benutzer '),
('WelcomeForum2','wrote: ','hat geschrieben: '),
('WelcomeForum3','send message','Naricht senden'),
('WelcomeForum4','type a new message','Schreibe eine neue Naricht');