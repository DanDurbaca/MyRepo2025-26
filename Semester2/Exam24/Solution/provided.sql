create or replace database examWSERS2;
use examWSERS2;

CREATE TABLE People (
    PersonId int not null primary key auto_increment,
    Name varchar(25) unique,
    Money int not null  
);

create table Promotions(
    PromoId int not null primary key auto_increment,
    Code varchar(25) unique,
    Value int not null,
    Available int not null
);


