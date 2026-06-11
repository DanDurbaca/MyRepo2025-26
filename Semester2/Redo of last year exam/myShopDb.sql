
drop database ShopExam;

create database ShopExam;

use ShopExam;

create table Categories(
    categoryId int not null auto_increment primary key,
    categoryName varchar(50) unique not null
);

create table ShopProducts(
    productId int not null auto_increment primary key,
    ProductName varchar(50),
    Price int,
    Inventory int,
    ImageLink varchar(255),
    categoryId int not null,
    FOREIGN KEY (categoryId) REFERENCES Categories(categoryId)
);

Insert into Categories (categoryId, categoryName) values
(1, 'Vegetables'),
(2, 'Fruits'),
(3, 'Dairy'),
(4, 'Meat'),
(5, 'Bakery'),
(6, 'TEST CATEGORY');


Insert into ShopProducts (productId, ProductName, Price, Inventory, ImageLink, categoryId) values
(1, 'Potato', 2, 100, 'Potato.jpg', 1),
(2, 'Apple', 3, 50, 'Apple.jpg', 2),
(3, 'Milk', 1.5, 200, 'Milk.jpg', 3),
(4, 'Chicken', 5, 30, 'Chicken.jpg', 4),
(5, 'Bread', 1.2, 150, 'Bread.jpg', 5),
(6, 'Carrot', 2.5, 80, 'Carrot.jpg', 1),
(7, 'Banana', 1.8, 60, 'Banana.jpg', 2),
(8, 'Cheese', 4, 40, 'Cheese.jpg', 3),
(9, 'Beef', 6, 20, 'Beef.jpg', 4),
(10, 'Croissant', 1.5, 100, 'Croissant.jpg', 5),
(11, 'Spinach', 2, 90, 'Spinach.jpg', 1),
(12, 'Orange', 2.5, 70, 'Orange.jpg', 2),
(13, 'Yogurt', 1.8, 120, 'Yogurt.jpg', 3),
(14, 'Pork', 5.5, 25, 'Pork.jpg', 4),
(15, 'Baguette', 1.8, 110, 'Baguette.jpg', 5);

