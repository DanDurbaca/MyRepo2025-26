create or replace database RawIngredients;

use RawIngredients;

create table SomeIngredients(
    ingredientId int primary key auto_increment,
    ingredientName varchar(20),
    ingredientCalories int not null
);


insert into SomeIngredients(ingredientName,ingredientCalories) VALUES
("Carrots",10),
("Peas",20),
("Broccoli",30),
("Spinach",40),
("Tomatoes",50),
("Cucumbers",60),
("Onions",70),
("Garlic",80),
("Bell Peppers",90),
("Mushrooms",100),
("Zucchini",110),
("Eggplant",120),
("Asparagus",130),
("Cauliflower",140),
("Brussels Sprouts",150),
("Green Beans",160),
("Corn",170),
("Potatoes",180),
("Sweet Potatoes",190),
("Avocado",200);