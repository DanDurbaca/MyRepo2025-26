
create or replace database FirstDb2026;
use FirstDb2026;


create table Ingredients(
    ingredientId int primary key auto_increment,
    ingredientName varchar(20),
    ingredientCalories int
);

insert into Ingredients(ingredientName,ingredientCalories) VALUES 
("Carrots",10),
("Peas",20),
("Brocoli",15),
("Spinach",25),
("Tomatoes",30);

