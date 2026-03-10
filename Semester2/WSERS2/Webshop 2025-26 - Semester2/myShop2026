create or replace database myShop2026;
use myShop2026;

-- Create the translations table
CREATE TABLE translations (
    id        INTEGER PRIMARY KEY AUTO_INCREMENT,
    myKey       VARCHAR(100)  NOT NULL UNIQUE,
    english   VARCHAR(500)  NOT NULL,
    romanian  VARCHAR(500)  NOT NULL
);

-- Insert all translation rows
INSERT INTO translations (myKey, english, romanian) VALUES
    ('HomeBtn',           'Home',                              'Acasă'),
    ('ContactBtn',        'Contact',                           'Contact'),
    ('ProductBtn',        'Products',                          'Produse'),
    ('RegisterBtn',       'Register',                          'Înregistrează-te'),
    ('HomeText',          'Welcome to our shop. This should be a great day', 'Bine ați venit în magazinul nostru. Ar trebui să fie o zi grozavă'),
    ('ContactText',       'Contact us',                        'Contactați-ne'),
    ('ContactInfo',       'This is our contact information...','Acestea sunt informațiile noastre de contact...'),
    ('EmailLabel',        'Email Address',                     'Adresa de email'),
    ('PhoneLabel',        'Phone Number',                      'Număr de telefon'),
    ('AddressLabel',      'Address',                           'Adresă'),
    ('FirstNameLabel',    'First Name',                        'Prenume'),
    ('PasswordLabel',     'Your password:',                    'Parola dvs.:'),
    ('SendBtn',           'Send this data',                    'Trimite aceste date'),
    ('OurProductsText',   'Our Products',                      'Produsele noastre'),
    ('ApplesProduct',     'Apples - fresh and juicy',          'Mere - proaspete și suculente'),
    ('BananasProduct',    'Bananas - rich in potassium',       'Banane - bogate în potasiu'),
    ('CarrotsProduct',    'Carrots - good for your eyesight',  'Morcovi - buni pentru vedere'),
    ('TomatoesProduct',   'Tomatoes - perfect for salads',     'Roșii - perfecte pentru salate'),
    ('PotatoesProduct',   'Potatoes - versatile and filling',  'Cartofi - versatili și sățioși'),
    ('RegisterText',      'Create your account',               'Creează-ți contul'),
    ('PickUsernameLabel', 'Pick a username:',                  'Alege un nume de utilizator:'),
    ('PickPasswordLabel', 'Pick a password:',                  'Alege o parolă:'),
    ('RegisterBtnNow',    'Register now',                      'Înregistrează-te acum'),
    ('LoginBtn',          'Login',                             'Login');


-- Create the products table
CREATE TABLE products (
    id              INTEGER         PRIMARY KEY AUTO_INCREMENT,
    product_name_en VARCHAR(100)    NOT NULL,
    product_name_ro VARCHAR(100)    NOT NULL,
    image_link      VARCHAR(255)    NOT NULL,
    price           DECIMAL(10, 2)  NOT NULL,
    description_en  VARCHAR(500)    NOT NULL,
    description_ro  VARCHAR(500)    NOT NULL
);

-- Insert all product rows
INSERT INTO products (product_name_en, product_name_ro, image_link, price, description_en, description_ro) VALUES
    ('Apples',   'Mere',    'Apples.jpg',   2.00, 'They are juicy and red',    'Sunt suculente și roșii'),
    ('Bananas',  'Banane',  'Bananas.jpg',  1.50, 'Rich in potassium',         'Bogate în potasiu'),
    ('Carrots',  'Morcovi', 'Carrots.jpg',  1.20, 'Good for eyesight',         'Bune pentru vedere'),
    ('Potatoes', 'Cartofi', 'Potatoes.jpg', 0.80, 'They grow in the ground',   'Cresc în pământ'),
    ('Tomatoes', 'Roșii',   'Tomatoes.jpg', 2.50, 'Perfect for salads',        'Perfecte pentru salate');


-- Create the users table
CREATE TABLE users (
    id       INTEGER      PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert all user rows
INSERT INTO users (username, password) VALUES
    ('Toto', '111'),
    ('Joe',  '123');