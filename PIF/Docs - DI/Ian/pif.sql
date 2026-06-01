

CREATE TABLE user (
    pk_username VARCHAR(50) PRIMARY KEY,
    fullName VARCHAR(50) NOT NULL,
    emailAddress VARCHAR(50) NOT NULL UNIQUE,
    isAdmin BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE station (
    pk_stationID VARCHAR(20) PRIMARY KEY,
    name VARCHAR(50),
    description VARCHAR(100),
    fk_userID VARCHAR(50) NOT NULL,
    FOREIGN KEY (fk_userID) REFERENCES user(pk_username) ON DELETE CASCADE
);

CREATE TABLE measurement (
    pk_measurementID INT PRIMARY KEY,
    timestamp TIMESTAMP NOT NULL,
    temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    airPressure DECIMAL(7,2),
    lightIntensity INTEGER,
    airQuality INTEGER,
    fk_stationID VARCHAR(20) NOT NULL,
    FOREIGN KEY (fk_stationID) REFERENCES station(pk_stationID) ON DELETE CASCADE
);

CREATE TABLE collections (
    pk_collectionID INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    fk_userID VARCHAR(50) NOT NULL,
    FOREIGN KEY (fk_userID) REFERENCES user(pk_username) ON DELETE CASCADE
);

-- Junction table for Collections <-> Measurements
CREATE TABLE collection_measurements (
    pkfk_collectionID INT,
    pkfk_measurementID INT,
    PRIMARY KEY (pkfk_collectionID, pkfk_measurementID),
    FOREIGN KEY (pkfk_collectionID) REFERENCES collections(pk_collectionID) ON DELETE CASCADE,
    FOREIGN KEY (pkfk_measurementID) REFERENCES measurement(pk_measurementID) ON DELETE CASCADE
);

-- Junction table for User Friends
CREATE TABLE user_friends (
    pkfk_user1ID VARCHAR(50),
    pkfk_user2ID VARCHAR(50),
    PRIMARY KEY (pkfk_user1ID, pkfk_user2ID),
    FOREIGN KEY (pkfk_user1ID) REFERENCES user(pk_username) ON DELETE CASCADE,
    FOREIGN KEY (pkfk_user2ID) REFERENCES user(pk_username) ON DELETE CASCADE,
    CHECK (pkfk_user1ID <> pkfk_user2ID) -- Prevent self-friending
);

-- Junction table for Sharing Collections
CREATE TABLE collection_shares (
    pkfk_collectionID INT,
    pkfk_userID VARCHAR(50),
    PRIMARY KEY (pkfk_collectionID, pkfk_userID),
    FOREIGN KEY (pkfk_collectionID) REFERENCES collections(pk_collectionID) ON DELETE CASCADE,
    FOREIGN KEY (pkfk_userID) REFERENCES user(pk_username) ON DELETE CASCADE
);