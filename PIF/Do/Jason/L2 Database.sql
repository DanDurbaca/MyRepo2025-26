drop database if exists PIF;
create database PIF;
use PIF;

-- Users
create table `User`(
    user_ID int not null auto_increment primary key,
    full_name varchar(255),
    administrator int,
    email_address varchar(255),
    friends int,
    Upswd varchar(255),
    UName varchar(255)
);

-- Friendlist
create table Friendlist (
    friendlist_ID int not null auto_increment primary key,
    user int
); 

-- Friendship relations table for accepted friends
create table Friendship (
    user_id int not null,
    friend_id int not null,
    primary key (user_id, friend_id),
    foreign key (user_id) references User(user_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    foreign key (friend_id) references User(user_ID) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- Friend requests table for pending/accepted/refused requests
create table FriendRequests (
    request_ID int not null auto_increment primary key,
    sender_id int not null,
    receiver_id int not null,
    status enum('pending','accepted','refused') not null default 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    foreign key (sender_id) references User(user_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    foreign key (receiver_id) references User(user_ID) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- add FK after both tables exist (handles circular relation)
ALTER TABLE Friendlist
  ADD CONSTRAINT fk_friendlist_user FOREIGN KEY (user) REFERENCES User(user_ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE User
  ADD CONSTRAINT fk_user_friends FOREIGN KEY (friends) REFERENCES Friendlist(friendlist_ID) ON DELETE SET NULL ON UPDATE CASCADE;

-- Stations
create table Station (
    serial_number int not null primary key,
    station_name varchar(255),
    station_description varchar(255),
    user_station int,
    foreign key (user_station) references User(user_ID) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- Collections
create table Collection (
    collection_ID int not null auto_increment primary key,
    collection_name varchar(255),
    station_description varchar(255)
); 

-- Measurements (keeps measurement_ID PK and a DATETIME timestamp)
create table Measurement (
    measurement_ID int not null auto_increment primary key,
    timestamp_Measurement DATETIME DEFAULT CURRENT_TIMESTAMP,
    temperature INT,
    humidity INT,
    airpressure INT,
    lightintensity INT,
    airquality INT,
    station INT,
    FOREIGN KEY (station) REFERENCES Station(serial_number) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- User <-> Collection mapping
create table User_Collections (
    user int,
    collection_ID INT,
    foreign key (user) references User(user_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    foreign key (collection_ID) references Collection(collection_ID) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- Collection <-> Measurements mapping: reference measurement_ID (was incorrectly referencing timestamp)
create table Collection_Measurements (
    collection_ID INT,
    measurement INT,
    foreign key (collection_ID) references Collection(collection_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    foreign key (measurement) references Measurement(measurement_ID) ON DELETE CASCADE ON UPDATE CASCADE
); 

-- Seed data (order chosen to satisfy FKs)
INSERT INTO Friendlist (user) VALUES
(NULL),   -- friendlist 1
(NULL),   -- friendlist 2
(NULL);   -- friendlist 3

INSERT INTO User (full_name, administrator, email_address, friends, Upswd, UName) VALUES
('Mingus John', 1, 'Mingus@example.com', 1, '1', 'MingusJ'),
('Tom Marley', 0, 'Tom@example.com', 2 , '1', 'TomM'),
('Huy Nguyen', 0, 'Huy@example.com', 3, '1', 'HuyN'),
('admin', 1, 'admin@example.com', 3, '1', 'admin');

-- link friendlist rows back to users
UPDATE Friendlist SET user = 1 WHERE friendlist_ID = 1;
UPDATE Friendlist SET user = 2 WHERE friendlist_ID = 2;
UPDATE Friendlist SET user = 3 WHERE friendlist_ID = 3;

INSERT INTO Station (serial_number, station_name, station_description, user_station) VALUES
(1, 'Weather Station 1', 'Living room', 1),
(2, 'Weather Station 2', 'Backyard', 2),
(3, 'Weather Station 3', 'Dining room', 3),
(5, 'New Station', 'Unassigned', NULL);

INSERT INTO Collection (collection_name, station_description) VALUES
('Temperature Logs', 'Daily temperature readings'),
('Moisture Reports', 'Soil moisture measurements'),
('Flow Records', 'River flow level logs');

INSERT INTO User_Collections (user, collection_ID) VALUES
(1, 1),
(2, 2),
(3, 3);

-- Insert sample measurements so Collection_Measurements FK entries are valid.
-- measurement_ID will be 1..6 in this insert order.
INSERT INTO Measurement (timestamp_Measurement, temperature, humidity, airpressure, lightintensity, airquality, station) VALUES
('2026-03-01 08:00:00', 21, 45, 1012, 300, 12, 1),
('2026-03-01 12:00:00', 23, 42, 1010, 500, 10, 1),
('2026-03-02 09:00:00', 19, 55, 1008, 200, 20, 3),
('2026-03-02 15:00:00', 20, 50, 1009, 250, 18, 3),
('2026-03-03 07:30:00', 16, 60, 1005, 100, 30, 2),
('2026-03-03 18:45:00', 18, 58, 1007, 150, 25, 2);

-- Now map collections to measurement IDs (these IDs correspond to the six inserted rows)
INSERT INTO Collection_Measurements (collection_ID, measurement) VALUES
(1, 1),
(1, 2),
(2, 5),
(2, 6),
(3, 3),
(3, 4);