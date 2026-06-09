drop database Webshopdb;
create database Webshopdb;
use Webshopdb;

create table products (
    productID int primary key auto_increment,
    productNameEN varchar(30),
    ImageLink varchar(100),
    productPrice DECIMAL(10,2),
    descriptionEN varchar(200),
    descriptionPT varchar(200),
    productNamePT varchar(30)
);
INSERT INTO products(productNameEN, ImageLink, productPrice, descriptionEN, descriptionPT, productNamePT) VALUES
('Brake Pads','brakepads.jpg',39.99,'High-quality brake pads for reliable stopping power.','Pastilhas de travão de alta qualidade para potência de travagem confiável.','Pastilhas de travão'),
('Oil Filter','oilfilter.jpg',14.99,'Durable oil filter to protect your engine and prolong oil life.','Filtro de óleo durável para proteger o motor e prolongar a vida do óleo.','Filtro de óleo'),
('Car Battery','battery.jpg',89.99,'Long-lasting car battery with strong cold-crank performance.','Bateria de longa duração com boa performance em arranque a frio.','Bateria de carro'),
('Spark Plugs','sparkplugs.jpg',24.99,'Set of 4 spark plugs for consistent ignition and improved efficiency.','Conjunto de 4 velas para ignição consistente e melhor eficiência.','Velas de ignição (4)'),
('Air Filter','airfilter.jpg',19.99,'High-efficiency air filter to improve engine airflow and protection.','Filtro de ar de alta eficiência para melhorar o fluxo de ar e proteger o motor.','Filtro de ar'),
('Turbo','turbo.jpg',299.99,'Performance turbo to increase engine power and throttle response.','Turbo de desempenho para aumentar a potência do motor e a resposta do acelerador.','Turbo'),
('Muffler','muffler.jpg',59.99,'Exhaust muffler designed to reduce noise while preserving flow.','Silenciador de escape projetado para reduzir o ruído mantendo o fluxo.','Silenciador'),
('Cabin Filter','cabinfilter.jpg',24.99,'Cabin (pollen) filter to keep interior air clean and fresh.','Filtro de cabine para manter o ar interior limpo e fresco.','Filtro de cabine'),
('Exhaust Pipe','exhaustpipe.jpg',89.99,'High-grade exhaust pipe for improved flow and durability.','Tubo de escape de alta qualidade para melhor fluxo e durabilidade.','Tubo de escape'),
('Radiator','radiator.jpg',129.99,'Efficient radiator to help maintain optimal engine temperature.','Radiador eficiente para ajudar a manter a temperatura ideal do motor.','Radiador'),
('Alternator','alternator.jpg',149.99,'Reliable alternator to keep the electrical system and battery charged.','Alternador confiável para manter o sistema elétrico e a bateria carregados.','Alternador'),
('Fuel Pump','fuelpump.jpg',89.99,'High-pressure fuel pump for consistent fuel delivery under load.','Bomba de combustível de alta pressão para entrega consistente sob carga.','Bomba de combustível'),
('Timing Belt','timingbelt.jpg',49.99,'Durable timing belt ensuring correct engine timing and longevity.','Correia de distribuição durável que garante sincronização correta e durabilidade.','Correia de distribuição'),
('Headlight','headlight.jpg',39.99,'Bright headlight for improved night-time visibility and safety.','Farol potente para melhor visibilidade noturna e segurança.','Farol'),
('Windshield Wiper','wiper.jpg',14.99,'Durable wiper blade for clear visibility in rain and snow.','Palheta resistente para visibilidade clara na chuva e neve.','Palheta de para-brisas'),
('Shock Absorber','shockabsorber.jpg',59.99,'Comfort-oriented shock absorber for a smoother ride and better handling.','Amortecedor orientado para conforto para uma condução mais suave e melhor controlo.','Amortecedor'),
('Clutch Kit','clutchkit.jpg',129.99,'Complete clutch kit for reliable gear engagement and long life.','Kit de embraiagem completo para engate fiável e longa duração.','Kit de embraiagem'),
('Wheel Bearing','wheelbearing.jpg',34.99,'Durable wheel bearing for stable wheel rotation and safety.','Rolamento de roda durável para rotação estável e segurança.','Rolamento de roda'),
('Starter Motor','startermotor.jpg',99.99,'Powerful starter motor for dependable engine starts every time.','Motor de arranque potente para partidas confiáveis sempre.','Motor de arranque'),
('Water Pump','waterpump.jpg',49.99,'Reliable water pump to maintain coolant circulation and engine temperature.','Bomba de água confiável para manter a circulação do líquido de arrefecimento e a temperatura do motor.','Bomba de água'),
('Engine Belt','engineBelt.jpg',80.00,'An engine belt, also known as a serpentine belt or drive belt, is a crucial component that transfers power from the engine''s crankshaft to various accessories, ensuring their proper operation.','Uma correia do motor, também conhecida como correia serpentina ou correia de transmissão, é um componente crucial que transfere potência do veio do motor para diversos acessórios, garantindo o seu funcionamento adequado.','Correia de motor');

create table Clients (
    clientID int primary key auto_increment,
    Username varchar(50) unique,
    usrpassword varchar(300),
    Email varchar(100),
    date_of_birth date,
    isadmin boolean NOT NULL DEFAULT FALSE
);

create table orders (
    OrderID int primary key auto_increment,
    clientId int,
    OrderStatus varchar(50) DEFAULT 'Pending',
    FOREIGN KEY(clientId) REFERENCES clients (clientID)
);

create table boghtItem (
idb int primary key auto_increment,
quantity int,
productId int,
orderId int,
FOREIGN KEY(productId) REFERENCES products (productID),
FOREIGN KEY(orderId) REFERENCES orders (OrderID)
);

create table Messages(
    id INT primary key AUTO_INCREMENT,
    messageText varchar(255),
    username varchar(50) not null
);

INSERT INTO Clients(Username, usrpassword, Email, date_of_birth, isadmin) VALUES
('admin', '$2y$10$aswhQ8Yhp4qnRxSxaFSxwei/Xfg5zvMwZ4K.04jwwz25gosRXiBz6', 'admin@example.com', '2007-05-15', TRUE),
("customer111","$2y$10$Ct2XsmocasWp8Ee0qHA4MelhEeAwvd.LbN4U9SJSmcspv4cysoeTu","DiaAd524@school.lu","2025-12-26", FALSE);

CREATE TABLE translations (
    translation_key VARCHAR(100) PRIMARY KEY,
    EnglishText TEXT NOT NULL,
    PortugueseText TEXT NOT NULL
);

INSERT INTO translations (translation_key, EnglishText, PortugueseText)
VALUES
('HomeBtn','Home','Casa'),
('ContactBtn','Contact','Contacto'),
('ProductBtn','Product','Prudutos'),
('LogInBtn','Log In','Iniciar Sessão'),
('RegisterBtn','Register','Registrar'),
('HomeTextH1','Welcome to MyModificationGarage','Bem vindo a MyModificationGarage'),
('HomeText','Discover amazing modification and parts for your car, contact us for more info, or browse our latest offers. Use the navigation bar above to explore.','Descubra modificações e peças incríveis para o seu carro, contacte-nos para mais informações ou consulte as nossas últimas ofertas. Utilize a barra de navegação acima para explorar.'),
('ContactTextH1','Contact Information','Informacão de Contacto'),
('Phonetrnsl','Phone:','Telefone:'),
('addresstrnsl','address:','endereço:'),
('Fnametrnsl','First name:','Primeiro nome:'),
('Lnametrnsl','Last name:','Ultimo nome:'),
('sppWlcH1','welcome to our suport ','Bem vindo ao nosso suporte '),
('sppWlctxt','Feel free to reach out to us with any questions or feedback!','Sinta-se à vontade para nos contactar com quaisquer perguntas ou comentários!'),
('RgH1','Registration Form','Formulário de Registo'),
('Rgprogress','registration in progress...','registo em progresso...'),
('Rgwelcome','Welcome you are now registered!','Bem-vindo, você está agora registado!'),
('Rgpssw!','Passwords do not match or user alredy exists, please try again.','As palavras-passe não coincidem ou o utilizador já existe, por favor tente novamente.'),
('Rgusrn','Choose a User Name','Escolha um nome de utilizador'),
('Rgpssw','Choose a password','Escolha uma senha'),
('Rgpssw2','Repeat the password','Repita a senha'),
('lgnusr','User Name:','Nome de usuario:'),
('lgnpssw','Password:','Senha:'),
('Rgdob','Date of birth:','Data de nascimento:'),
('lgncheck','Checking...','A confirmar...'),
('lgnsuccess','Login successful. you''ll be directed to the your account in moments.','Sessão iniciada com sucesso. Será direcionado a pagina principal em momentos.'),
('lgnfail','Invalid username or password.','Nome de usuario ou senha invalido.'),
('CartBtn','Cart','Carrinho'),
('CartTextH1','Shopping Cart','Carrinho de Compras'),
('CartText','Your shopping cart is currently empty. Browse our products and add items to your cart!','Seu carrinho de compras está vazio. Navegue pelos nossos produtos e adicione itens!'),
('CartEmpty','Your cart is empty','Seu carrinho está vazio');