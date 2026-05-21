drop database WebShop;
create database WebShop;
use WebShop;

create table Products (
    id int auto_increment primary key,
    ProductNameEN VARCHAR(255),
    ImageLink VARCHAR(255),
    Price VARCHAR(20),
    DescriptionEN TEXT,
    DescriptionRU TEXT,
    ProductNameRU VARCHAR(255)
);

create table Translations (
    id int auto_increment primary key,
    TranslationsKey VARCHAR(255),
    EnglishText VARCHAR(255),
    RussianText VARCHAR(255)
);

create table Client (
    id int auto_increment primary key,
    Username VARCHAR(255) not null unique,
    UserPassword VARCHAR(255),
    UserAdmin CHAR(3)
);

create table Messages (
    id int auto_increment primary key,
    MessageText VARCHAR(255),
    Username VARCHAR(255) not null,
    Foreign key (Username) references Client(Username)
);
insert into Products 
(ProductNameEN, ImageLink, Price, DescriptionEN, DescriptionRU, ProductNameRU)
values
('Air Jordan 7', 'Shoe_1.webp', '699£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Эйр Джордан 7'),
('Air Jordan 4', 'Shoe_2.webp', '349£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Эйр Джордан 4'),
('Air Jordan 3', 'Shoe_3.jpg', '199£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Эйр Джордан 3'),
('Air Jordan 5', 'Shoe_4.jpg', '799£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Эйр Джордан 5'),
('Air Jordan 7', 'Shoe_5.webp', '269£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Эйр Джордан 7'),
('Air Jordan 2', 'Shoe_6.webp', '169£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Эйр Джордан 2'),
('Jordan Running', 'Shoe_7.png', '267£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Джордан Раннинг'),
('Nike Schube', 'Shoe_8.jpg', '139£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Найк Шубе'),
('Nike Low Dunk 3', 'Shoe_9.webp', '149£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Найк Лоу Данк 3'),
('Nike Cortez Pink', 'Shoe_10.webp', '199£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Найк Кортез Розовые'),
('Nike DownShifter', 'Shoe_11.webp', '159£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Найк Дауншифтер'),
('Nike Lebron Witness', 'Shoe_12.webp', '249£', 'They are stylish and iconic.', 'Они стильные и культовые.', 'Найк Леброн Уитнесс');

insert into Translations
(TranslationsKey, EnglishText, RussianText) 
values
('HomeBtn','Home','Главная'),
('ContactBtn','Contact','Страница контактов'),
('ProductBtn','Products','Продукты'),
('RegisterBtn','Register','Регистрация'),
('HomeText','Welcome to Our Web Shop!','Добро пожаловать в наш интернет-магазин!'),
('HomeP','Browse our products and enjoy your shopping experience.','Просмотрите наши товары и наслаждайтесь процессом покупок.'),
('ContactT','This is our contact information','Это наша контактная информация'),
('ContactEmail','Email:','Электронная почта:'),
('ContactPhone','Phone:','Телефон:'),
('ContactAddress','Address:','Адрес:'),
('ContactHours','Business Hours:','Часы работы:'),
('ProductsT','This are our products:','Это наши продукты:'),
('RegistrationForm','Registration Form','Форма регистрации'),
('RegistrationName','Choose an User Name','Выберите имя пользователя'),
('RegistrationPassword','Choose a password','Выберите пароль'),
('RegistrationEmail','Email','Электронная почта'),
('RegistrationPhone','Phone','Телефон'),
('LoginBtn','Login','Войти'),
('logincheck','Checking','Проверка'),
('loginsuccess','You logged in successfully','Вы успешно вошли в систему'),
('loginfail','Your login failed','Вход не выполнен'),
('loginuser','User','Пользователь'),
('loginpassword','Password','Пароль'),
('LoginT','Login','Войти'),
('WelcomeBtn',' Welcome,',' Добро пожаловать,'),
('AdminBtn','Admin Panel','Администратор'),
('NameTextEn','English Name','Английское имя'),
('NameTextRu','Russian Name','Русское имя'),
('FileName','Image File Name (example: brakepads.jpg)','Имя файла изображения (пример: brakepads.jpg)'),
('Price','Price','Цена'),
('DescEN','English Description','Английское описание'),
('DescRU','Russian Description','Русское описание'),
('CreateBtn','Create Product','Создать продукт'),
('WelcomeText','Welcome,','Добро пожаловать, '),
('CartTitle', 'Your Cart', 'Ваша корзина'),
('CartSubtitle', 'Your selected items will appear here', 'Выбранные вами товары появятся здесь'),
('EmptyCart', 'Your cart is currently empty.', 'Ваша корзина сейчас пуста'),
('Total', 'Total', 'Итого'),
('Checkout', 'Checkout', 'Оформить заказ'),
('RemoveBtn','Remove','Удалить'),
('ItemName','Item','Товар'),
('Quantity','Quantity','Количество'),
('BuyBtn','Buy','Купить'),
('ForumText','Forum','Форум'),
('SendMessage','Send Message','Отправить'),
('NewMessage','New Message','Новое сообщение'),
('AllPreviousMessages','All Previous Messages','Все предыдущие сообщения');

insert into Client
(Username, UserPassword, UserAdmin)
values
('Admin', '$2y$10$dJMyu5MDp8tg5XeC6cUqOeSvFkyOQgsZdGCKr6PwuF5tCt/7g6TyC', 'yes'),
('uwu', '$2y$10$E1p.qJTeSYehDa20TQ3WtenfukIu6YkCs8c6VfElN6IFVBS5udO4C', 'no');
