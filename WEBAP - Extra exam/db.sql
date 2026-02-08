create database dbTestForExtraPoints;
use dbTestForExtraPoints;

CREATE TABLE Persons (
    PersonID int primary key auto_increment,
    LastName varchar(255),
    FirstName varchar(255),
    Address varchar(255),
    City varchar(255)
);


INSERT INTO Persons (LastName, FirstName, Address, City) VALUES
('Smith', 'John', '123 Main St', 'Anytown'),
('Doe', 'Jane', '456 Oak Ave', 'Sometown'),
('Brown', 'Charlie', '789 Pine Rd', 'Villecity'),
('Johnson', 'Emily', '101 Maple Blvd', 'Metropolis'),
('Dude', 'Dudovsky', '102 Maple Blvd', 'Metropolis');