create or replace database citiesAndCountries;

use citiesAndCountries;

CREATE TABLE Countries (
    CountryID INT PRIMARY KEY,
    NameOfCountry VARCHAR(50) NOT NULL
);

create Table Cities(
    CityID INT PRIMARY KEY,
    NameOfCity VARCHAR(50) not null,
    CountryID int not null,
    FOREIGN KEY (CountryID) REFERENCES Countries(CountryID)
);


insert into Countries (CountryID, NameOfCountry) values
(1, 'Luxembourg'),
(2, 'Belgium'),
(3, 'France'),
(4, 'Germany');

insert into Cities (CityID, NameOfCity, CountryID) values
(1, 'Luxembourg City', 1),
(2, 'Esch-sur-Alzette', 1),
(3, 'Differdange', 1),
(4, 'Brussels', 2),
(5, 'Antwerp', 2),
(6, 'Ghent', 2),
(7, 'Paris', 3),
(8, 'Lyon', 3),
(9, 'Marseille', 3),
(10, 'Berlin', 4),
(11, 'Munich', 4),
(12, 'Frankfurt', 4);
-- Example query to test the data