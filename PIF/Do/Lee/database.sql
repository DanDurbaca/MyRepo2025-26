DROP DATABASE IF EXISTS pif_db;
CREATE DATABASE pif_db;
USE pif_db;
set foreign_key_checks=0;

-- User table
CREATE TABLE User (
    userId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userName VARCHAR(255),
    firstName VARCHAR(255),
    lastName varchar (255),
    userRole BOOLEAN,
    emailAddress VARCHAR(255),
    password varchar(255)
);

-- Friendlist table (self-referential)
CREATE TABLE Friendlist (
    friendListId INT,
    user INT,
    requestStatus INT,
    PRIMARY KEY (friendListId, user),
    CONSTRAINT fk_friendlist_user FOREIGN KEY (user) REFERENCES User(userId),
    CONSTRAINT fk_friendlist_id FOREIGN KEY (friendListId) REFERENCES User(userId)
);

-- Station table
CREATE TABLE Station (
    serialNumber INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    stationName VARCHAR(255),
    descr VARCHAR(255),
    userId INT,
    CONSTRAINT fk_station_user FOREIGN KEY (userId) REFERENCES User(userId)
);

-- Collection table
CREATE TABLE Collection (
    collectionId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    collectionName VARCHAR(255),
    userId INT,
    descr VARCHAR(255),
    CONSTRAINT fk_collection_user FOREIGN KEY (userId) REFERENCES User(userId)
);

-- Measurement table
CREATE TABLE Measurement (
    measurementId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME,
    temperature INT,
    humidity INT,
    airPressure INT,
    lightIntensity INT,
    airQuality INT,
    station INT,
    CONSTRAINT fk_measurement_station FOREIGN KEY (station) REFERENCES Station(serialNumber)
);

-- UserCollections table
CREATE TABLE UserCollections (
    user INT,
    collectionId INT,
    CONSTRAINT fk_usercol_user FOREIGN KEY (user) REFERENCES User(userId),
    CONSTRAINT fk_usercol_col FOREIGN KEY (collectionId) REFERENCES Collection(collectionId)
);

-- CollectionMeasurements table
CREATE TABLE CollectionMeasurements (
    collectionId INT,
    measurement INT,
    CONSTRAINT fk_colmeas_col FOREIGN KEY (collectionId) REFERENCES Collection(collectionId),
    CONSTRAINT fk_colmeas_meas FOREIGN KEY (measurement) REFERENCES Measurement(measurementId)
);

-- UsersfriendListId
INSERT INTO User (userName, firstName, lastName, password, userRole, emailAddress) VALUES
("Widow", 'Natasha', 'Romanoff',"$2y$10$bALJ8OHa1Cqcfktj8Jwl5.aKeOjH2abdLu3FGcIBx2NjcEAJ1SYwO", 1, 'natasha@example.com'),
("Witch", 'Wanda', 'Maximoff',"$2y$10$YK7/1yVXoSJvHJH8Wbo6A.4JtYwoSj0oZ0aS65w6xQ4WqvNtk3TVi", 0, 'wanda@example.com'),
("Falcon", 'Sam', 'Wilson',"$2y$10$6j8B63HHBzZmg02GOKdGMuCrJ0vFXcvg//9MEaZmqpyWx09vwGds2", 0, 'sam@example.com');

-- Friendlists
INSERT INTO Friendlist (friendListId,user,requestStatus) VALUES
(1,2,0), 
(2,3,0), 
(3,1,0);

-- Stations
INSERT INTO Station (stationName, descr, userId) VALUES
('City Roof Weather Station', 'Measures temperature and wind', 1),
('Riverbank Flow Monitor', 'Tracks water levels', 2),
('Farm Field Soil Probe', 'Tracks moisture and nutrients', 3);

-- Collections
INSERT INTO Collection (collectionName, userId, descr) VALUES
('Morning Weather', 1, 'Temperature and humidity readings'),
('Soil Moisture Logs', 2, 'Soil moisture and air quality'),
('River Flow Records', 3, 'Daily river flow and pressure data');

-- Measurements
INSERT INTO Measurement (timestamp, temperature, humidity, airPressure, lightIntensity, airQuality, station) VALUES
('2025-12-04 06:00:00', 15, 70, 1012, 300, 50, 1),
('2025-12-04 12:00:00', 22, 60, 1010, 500, 40, 1),
('2025-12-04 08:00:00', 18, 65, 1008, 200, 35, 2),
('2025-12-04 18:00:00', 19, 68, 1007, 150, 38, 2),
('2025-12-04 07:00:00', 20, 55, 1005, 400, 30, 3),
('2025-12-04 20:00:00', 21, 50, 1004, 350, 25, 3),

-- Station 2 test measurements
('2025-12-05 06:00:00', 17, 72, 1006, 120, 40, 2),
('2025-12-05 12:00:00', 21, 60, 1009, 520, 36, 2),
('2025-12-05 18:00:00', 20, 65, 1008, 180, 38, 2),

('2025-12-06 06:00:00', 16, 75, 1007, 110, 42, 2),
('2025-12-06 12:00:00', 22, 58, 1011, 540, 35, 2),
('2025-12-06 18:00:00', 21, 63, 1010, 170, 37, 2),

('2025-12-07 06:00:00', 15, 78, 1009, 100, 45, 2),
('2025-12-07 12:00:00', 23, 55, 1012, 560, 34, 2),
('2025-12-07 18:00:00', 22, 60, 1011, 160, 36, 2),

('2025-12-08 06:00:00', 16, 74, 1010, 130, 41, 2),
('2025-12-08 12:00:00', 24, 53, 1013, 580, 33, 2),
('2025-12-08 18:00:00', 23, 58, 1012, 190, 35, 2),

('2025-12-09 06:00:00', 17, 70, 1011, 140, 39, 2),
('2025-12-09 12:00:00', 25, 52, 1014, 600, 32, 2),
('2025-12-09 18:00:00', 24, 57, 1013, 200, 34, 2),

('2025-12-10 06:00:00', 18, 68, 1012, 150, 38, 2),
('2025-12-10 12:00:00', 26, 50, 1015, 620, 31, 2),
('2025-12-10 18:00:00', 25, 55, 1014, 210, 33, 2);

-- UserCollections
INSERT INTO UserCollections (user, collectionId) VALUES
(1, 2),
(2, 3),
(3, 1);

-- CollectionMeasurements
INSERT INTO CollectionMeasurements (collectionId, measurement) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5),
(3, 6);

-- Station

create table translation (
    textID varchar(255) primary key,
    enText varchar(255),
    gerText varchar(255)
);

-- Translations used by REPIF pages. Removed unused/obsolete keys and added missing translations for visible UI strings.
INSERT INTO translation (textID, enText, gerText) VALUES
('navInd', 'Dashboard', 'Dashboard'),
('navSta', 'Stations', 'Stationen'),
('navCol', 'Collections', 'Kollectionen'),
('navReg', 'Register', 'Registrieren'),
('navLan', 'Change language', 'Wechsle Sprache'),

('regLogOut', 'Logout', 'Ausloggen'),
('regLogIn', 'Login', 'Einloggen'),
('regReg', 'Register', 'Registrieren'),
('regPas', 'Password', 'Passwort'),
('regPasCon', 'Confirm Password', 'Bestätige Passwort'),
('regCon', 'Confirm', 'Bestätige'),
('regCre', 'Create account', 'Kreiere Account'),
('regPasEmail', 'Email', 'Email'),

('staAdd', 'Edit', 'Edit'),

-- Added translations for hard-coded UI strings found in REPIF pages
('yourStations', 'Your stations', 'Deine Stationen'),
('addStation', 'Add Station', 'Station hinzufügen'),
('filterDate', 'Filter by date', 'Nach Datum filtern'),
('dataLabel', 'Data:', 'Daten:'),
('changeStationName', 'Change Station Name', 'Stationsname ändern'),
('changeDescription', 'Change Description', 'Beschreibung ändern'),
('changeStationOwner', 'Change Station Owner', 'Stationseigentümer ändern'),
('changeUsername', 'Change Username', 'Benutzername ändern'),
('changeName', 'Change name', 'Namen ändern'),
('changeEmail', 'Change Email Address', 'E-Mail-Adresse ändern'),
('changeRole', 'Change Role', 'Rolle ändern'),
('changePassword', 'Change Password', 'Passwort ändern'),
('addFriend', 'Add a friend', 'Füge einen Freund hinzu'),
('friendRequests', 'Friend Requests', 'Freundschaftsanfragen'),
('yourFriends', 'Your current friends', 'Deine aktuellen Freunde'),
('accept', 'Accept', 'Akzeptieren'),
('endFriendship', 'End friendship', 'Freundschaft beenden'),
('createUser', 'Create User', 'Benutzer erstellen'),
('createStation', 'Create a new Station', 'Neue Station erstellen'),
('createUserPage', 'Create a new user', 'Neuen Benutzer erstellen'),
('changeCollectionName', 'Change collection name', 'Sammlungsname ändern'),
('stationEdited', 'Station data edited', 'Stationsdaten bearbeitet'),
('change', 'Change', 'Ändern'),
('allUsers', 'All users', 'Alle Benutzer'),
('timeRecorded', 'Time of recording', 'Aufnahmezeit'),
('measureData', 'Measure Data', 'Messdaten'),
('temperature', 'Temperature', 'Temperatur'),
('humidity', 'Humidity', 'Feuchtigkeit'),
('airPressure', 'Air pressure', 'Luftdruck'),
('lightIntensity', 'Light intensity', 'Lichtintensität'),
('airQuality', 'Air quality', 'Luftqualität'),
('yourCollections', 'Your Collections', 'Deine Sammlungen'),
('unshare', 'Unshare', 'Teilen aufheben'),
('allStations', 'All stations', 'Alle Stationen'),
('changeStation', 'Change station', 'Station ändern');


set foreign_key_checks=1;
