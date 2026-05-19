-- Database
CREATE OR REPLACE DATABASE Webshop2025_26;
USE Webshop2025_26;

-- Table Structure
CREATE TABLE Products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductNameEN VARCHAR(255),
    ImageLink VARCHAR(255),
    Price DECIMAL(10,2),
    DescriptionEN TEXT,
    EffectEN TEXT,
    DescriptionPT TEXT,
    EffectPT TEXT,
    ProductNamePT VARCHAR(255)
);

CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(100) NOT NULL UNIQUE,
    DisplayName VARCHAR(100),
    Email VARCHAR(255) NOT NULL UNIQUE,
    UserPassword VARCHAR(255) NOT NULL,
    UserType VARCHAR(50) NOT NULL
);

CREATE TABLE Messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    messageText VARCHAR(255),
    username VARCHAR(100) NOT NULL,
    FOREIGN KEY (username) REFERENCES Users (Username)
);

CREATE TABLE Translations (
    TranslationKey VARCHAR(100) PRIMARY KEY,
    EnglishText TEXT NOT NULL,
    PortugueseText TEXT NOT NULL
);

-- All Translations
INSERT INTO Translations (TranslationKey, EnglishText, PortugueseText) VALUES

-- Global / Navigation
('HomeBtn', 'Home', 'Início'),
('ContactBtn', 'Contact', 'Contacto'),
('ProductBtn', 'Products', 'Produtos'),
('RegisterBtn', 'Register', 'Registar'),
('LoginBtn', 'Login', 'Iniciar Sessão'),
('LogoutBtn', 'Logout', 'Sair'),
('AdminBtn', 'Admin Panel', 'Painel de Admin'),

-- Welcome Message
('WelcomeLabel', 'Welcome, ', 'Bem-vindo, '),

-- Admin Panel
('AdminTitle', 'Admin Panel - Create Product', 'Painel Admin - Criar Produto'),
('AdminNameEN', 'Product Name (EN)', 'Nome do Produto (EN)'),
('AdminNamePT', 'Product Name (PT)', 'Nome do Produto (PT)'),
('AdminPrice', 'Price (EUR)', 'Preço (EUR)'),
('AdminDescEN', 'Description (EN)', 'Descrição (EN)'),
('AdminEffectEN', 'Effect (EN)', 'Efeito (EN)'),
('AdminDescPT', 'Description (PT)', 'Descrição (PT)'),
('AdminEffectPT', 'Effect (PT)', 'Efeito (PT)'),
('AdminImage', 'Product Image (PNG or JPEG, max 5MB)', 'Imagem do Produto (PNG ou JPEG, máx 5MB)'),
('AdminAddBtn', 'Add Product', 'Adicionar Produto'),
('AdminSuccess', 'Product successfully added!', 'Produto adicionado com sucesso!'),
('AdminErrFields', 'Please fill in all required fields.', 'Por favor, preencha todos os campos obrigatórios.'),
('AdminErrAccess', 'Access denied. Admins only.', 'Acesso negado. Apenas para Admins.'),
('AdminPurchaseDisabled', 'Admin View: Purchase Disabled', 'Vista de Admin: Compra Desativada'),
('LoginToPurchase', 'Login to Purchase', 'Entre para Comprar'),

-- Home Page
('HomeTitle', 'Welcome to the Pixel Potion Shop!', 'Bem-vindo à Loja de Poções Pixel!'),
('HomeText', 'Step into a world where magic fizzes in glass bottles and every pixel sparkles with possibility. Whether you need motivation for Mondays, courage for your next adventure, or a quick excuse for that “forgotten” homework, our handcrafted potions are brewed with care and humor.', 'Entre num mundo onde a magia borbulha em frascos de vidro e cada pixel brilha com possibilidades. Quer precise de motivação para as segundas-feiras, coragem para a sua próxima aventura, ou uma desculpa rápida para aquele trabalho de casa "esquecido", as nossas poções são preparadas com cuidado.'),

-- Products Page
('ProductTitle', 'Our Products', 'Os Nossos Produtos'),
('BuyBtn', 'Buy', 'Comprar'),

-- Contact Page
('ContactTitle', 'Contact the Potion Master', 'Contacte o Mestre das Poções'),
('ContactText', 'Need help with your order or a custom brew idea? Send us a message below!', 'Precisa de ajuda com a sua encomenda ou uma ideia de mistura? Envie-nos uma mensagem abaixo!'),
('ContactLabel1', 'Adventurer Name:', 'Nome do Aventureiro:'),
('ContactLabel2', 'Raven Address (Email):', 'Endereço do Corvo (Email):'),
('ContactLabel3', 'Potion Inquiry:', 'Consulta sobre Poção:'),
('ContactLabel4', 'Your Message:', 'A Sua Mensagem:'),
('ContactSendBtn', 'Send Your Scroll ✉️', 'Enviar o Seu Pergaminho ✉️'),
('ContactPlaceholder1', 'e.g., Merlin', 'ex., Merlin'),
('ContactPlaceholder2', 'you@example.com', 'você@exemplo.com'),
('ContactPlaceholder3', 'Which potion calls to you?', 'Qual poção o chama?'),
('ContactPlaceholder4', 'Type your message...', 'Digite a sua mensagem...'),
('ContactEmailRegistration', 'potionMaster001@magicmail.com', 'potionMaster001@magicmail.com'),
('ContactAddress', '123 Alchemy Lane, Potionville', '123 Rua da Alquimia, Vila das Poções'),
('ContactLocation', 'Potionville, Enchanted Forest', 'Vila das Poções, Floresta Encantada'),

-- Registration Page 
('RegisterTitle', 'Register as an Apprentice Brewer', 'Registe-se como Aprendiz de fabricante de poções'),
('RegisterText', 'Join the guild and start your potion-making journey!', 'Junte-se à guilda e comece a sua jornada na fabricação de poções!'),
('RegisterUsername', 'Adventurer Name:', 'Nome do Aventureiro:'),
('RegisterEmail', 'Raven Address (Email):', 'Endereço do Corvo (Email):'),
('RegisterSecretPassword', 'Secret Word (Password):', 'Palavra Secreta (Senha):'),
('RegisterSecretPasswordRepeat', 'Repeat Secret Word:', 'Repita a Palavra Secreta:'),
('RegisterPageButton', 'Join the Guild! ✨', 'Junte-se à Guilda! ✨'),
('DisplayName', 'Display Name (Public):', 'Nome de Exibição (Público):'),
('RegisterUsernamePlaceholder', 'e.g., Merlin', 'ex., Merlin'),
('RegisterEmailPlaceholder', 'you@example.com', 'tu@exemplo.com'),
('DisplayNamePlaceholder', 'Your public name in the guild', 'O seu nome público na guilda'),

-- Login Page
('LoginTitle', 'Login to your magic account', 'Inicie sessão na sua conta mágica'),
('LoginUsername', 'Username', 'Nome de Utilizador'),
('LoginUsernameEnter', 'Enter your username', 'Digite seu nome de utilizador'),
('LoginPassword', 'Password', 'Senha'),
('LoginPasswordEnter', 'Enter your secret word', 'Digite sua palavra secreta'),
('LoginMessageSuccess', 'You are logged in, mighty alchemist!', 'Você está conectado, poderoso alquimista!'),
('LoginMessageError', 'Invalid username or password.', 'Nome de utilizador ou senha inválidos.'),
('LoginButton', 'Login', 'Iniciar Sessão'),

-- Shop Cart Page
('ShopCartTitle', 'Your Alchemy Satchel', 'Conteúdo do Carrinho'),
('CartEmptyMsg', 'Your satchel is empty, traveler...', 'O teu alforge está vazio, viajante...'),
('CartReturnBtn', 'Return to Shop', 'Voltar à Loja'),
('CartTablePotion', 'POTION', 'POÇÃO'),
('CartTableQty', 'QTY', 'QTD'),
('CartTablePrice', 'PRICE', 'PREÇO'),
('CartTableAction', 'REMOVE', 'REMOVER'),
('CartGrandTotal', 'GRAND TOTAL', 'TOTAL GERAL'),
('CartContinueBtn', 'Continue Shopping', 'Continuar a Comprar'),
('CartFinalizeBtn', 'Finalize Order', 'Finalizar Encomenda'),

-- Forum Page
('ForumTitle', 'Guild Forum', 'Fórum da Guilda'),
('ForumBtn', 'Forum', 'Fórum'),
('ForumPlaceholder', 'Share your wisdom...', 'Partilha a tua sabedoria...'),
('ForumPostBtn', 'POST', 'POSTAR'),
('ForumNoMessages', 'No scrolls found... be the first to speak!', 'Nenhum pergaminho encontrado... sê o primeiro!'),
('ForumLoginRequired', 'Only registered members may post. Please Login.', 'Apenas membros registados podem postar. Faça Login.');

-- Default Data
INSERT INTO Users (Username, DisplayName, Email, UserPassword, UserType) VALUES
('bib','bib','bib@gmail.com','$2y$10$ngp3A7AySsBFgdtZtdnLEeNkr4nUXB4s0yPEsyDkiiNe5qLR7USiu','regular'),
('larissa','issa','issa@gmail.com','$2y$10$iENtggjXpIB7wy/4OHsqueb.zQJlaO26nq7oWdJOy0w8ZbGJMhAlm','Admin');

-- Products
INSERT INTO products 
(ProductNameEN, Imagelink, Price, DescriptionEN, EffectEN, DescriptionPT, EffectPT, ProductNamePT, Rarity)
VALUES
('Eternal Phoenix Feather','phoenix_feather.png',75.00,'A shimmering, radiant feather that never stops smoldering.','Miracle Resurrection: Automatically revives the player with 25% Health.','Uma pena radiante e cintilante que nunca para de arder.','Ressurreição Milagrosa: Revive automaticamente o jogador com 25% de Vida.','Pena de Fênix Eterna','Epic');

select * from Products;
select * from Users;
select * from Translations;