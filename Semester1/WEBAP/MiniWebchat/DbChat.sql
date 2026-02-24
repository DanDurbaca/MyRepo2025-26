create or replace database MiniWebchat;

use MiniWebchat;

create table Users(
    userId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userName VARCHAR(50) NOT NULL UNIQUE
);

create table Messages(
    MessageId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    MessageText VARCHAR(500),
    userId INT NOT NULL,
     FOREIGN KEY (userId) REFERENCES Users(userId)
);