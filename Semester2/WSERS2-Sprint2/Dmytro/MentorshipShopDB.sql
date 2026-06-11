drop DATABASE MentorshipShop;
CREATE DATABASE IF NOT EXISTS MentorshipShop;
use MentorshipShop;

DROP TABLE IF EXISTS translations;

create table translations (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    contentID VARCHAR(255) NOT NULL,
    languageCode VARCHAR(8) NOT NULL,
    content TEXT NOT NULL,
    UNIQUE (contentID, languageCode)
);

create table IF NOT EXISTS clients (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    pass VARCHAR(255) NOT NULL,
    userType VARCHAR(32) NOT NULL,
    UNIQUE (email)
);

create table IF NOT EXISTS products (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    productNameEN VARCHAR(255) NOT NULL,
    productNameUA VARCHAR(255) NOT NULL,
    imageLink VARCHAR(255),
    price VARCHAR(32) NOT NULL,
    descriptionEN TEXT NOT NULL,
    descriptionUA TEXT NOT NULL
);

CREATE TABLE orders (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES clients(ID)
);

CREATE TABLE order_items (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(ID),
    FOREIGN KEY (product_id) REFERENCES products(ID)
);

create table IF NOT EXISTS forumMsgs (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    msg TEXT NOT NULL,
    clientID INT NOT NULL,
    FOREIGN KEY (clientID) REFERENCES clients(ID)
);

INSERT INTO translations (contentID, languageCode, content) VALUES
('HomeBtn', 'en', 'home'),
('HomeBtn', 'uk', 'головна'),

('ProductBtn', 'en', 'mentors'),
('ProductBtn', 'uk', 'ментори'),

('ContactBtn', 'en', 'contact'),
('ContactBtn', 'uk', 'зв''язатися'),

('RegisterBtn', 'en', 'register'),
('RegisterBtn', 'uk', 'зареєструватися'),

('LoginBtn', 'en', 'login'),
('LoginBtn', 'uk', 'увійти'),

('LogoutBtn', 'en', 'Log out'),
('LogoutBtn', 'uk', 'Вихід'),

('Mentor', 'en', 'Choose Your Mentor'),
('Mentor', 'uk', 'Вибери наставника'),

('WelcomeSlogan1', 'en', 'Here you don''t buy things. You buy development.'),
('WelcomeSlogan1', 'uk', 'Тут не продають речі. Тут купують розвиток.'),

('WelcomeQuestion', 'en', 'Who are you today?'),
('WelcomeQuestion', 'uk', 'Хто ти сьогодні?'),

('WelcomeChoose', 'en', 'Choose a mentor for your path.'),
('WelcomeChoose', 'uk', 'Обери наставника під свій шлях.'),

('WelcomeFocus', 'en', 'Strength, style, code, discipline, rhythm — what do you want to train?'),
('WelcomeFocus', 'uk', 'Сила, стиль, код, дисципліна, ритм — що ти хочеш прокачати?'),

('WelcomeStartBtn', 'en', 'Go to mentors'),
('WelcomeStartBtn', 'uk', 'Перейти до наставників'),

('WelcomeNoTomorrow', 'en', 'There is no "I''ll start tomorrow"'),
('WelcomeNoTomorrow', 'uk', 'Немає «почну завтра»'),

('WelcomeChange', 'en', 'You either change yourself today — or you stay in place.'),
('WelcomeChange', 'uk', 'Ти або змінюєш себе сьогодні — або стоїш на місці.'),

('Contact', 'en', 'Contact us'),
('Contact', 'uk', 'Зв’яжіться з нами'),

('UnameCnt', 'en', 'Your username'),
('UnameCnt', 'uk', 'Ваш логін'),

('EmailCnt', 'en', 'Your email'),
('EmailCnt', 'uk', 'Ваша електронна адреса'),

('Msg', 'en', 'Message'),
('Msg', 'uk', 'Повідомлення'),

('SendCnt', 'en', 'Send message'),
('SendCnt', 'uk', 'Надіслати повідомлення'),

('Key', 'en', 'English'),
('Key', 'uk', 'Ukrainian'),

('RegForm', 'en', 'Registration form'),
('RegForm', 'uk', 'Форма реєстрації'),

('UnameReg', 'en', 'Username'),
('UnameReg', 'uk', 'Ім’я користувача'),

('EmailReg', 'en', 'Email'),
('EmailReg', 'uk', 'Електронна пошта'),

('Password', 'en', 'Password'),
('Password', 'uk', 'Пароль'),

('PasswordConf', 'en', 'Password confirmation'),
('PasswordConf', 'uk', 'Підтвердження пароля'),

('SendReg', 'en', 'Send'),
('SendReg', 'uk', 'Надіслати'),

('LoginLogin', 'en', 'Login'),
('LoginLogin', 'uk', 'Вхід'),

('UEmail', 'en', 'Email'),
('UEmail', 'uk', 'Електронна адреса'),

('PasswordLogin', 'en', 'Password'),
('PasswordLogin', 'uk', 'Пароль'),

('RegTaken', 'en', 'email is already taken'),
('RegTaken', 'uk', 'електронна адреса користувача вже зайнята'),

('EmptyIn', 'en', 'one of the fields was not filled'),
('EmptyIn', 'uk', 'одне з полів не було заповнене'),

('RegInvalidEmail', 'en', 'invalid email input'),
('RegInvalidEmail', 'uk', 'неправильно введена електронна адреса'),

('RegPassNotConf', 'en', 'Password is not confirmed'),
('RegPassNotConf', 'uk', 'Пароль не підтверджено'),

('RegSuccess', 'en', 'Registration has been successfully completed'),
('RegSuccess', 'uk', 'Реєстрацію успішно завершено'),

('LoginSuccess', 'en', 'Login has been successfully completed'),
('LoginSuccess', 'uk', 'Вхід успішно виконано'),

('LoginInvalid', 'en', 'Email or password do not match.'),
('LoginInvalid', 'uk', 'Електронна адреса або пароль не збігаються'),

('MentorsH1', 'en', 'Mentors'),
('MentorsH1', 'uk', 'Ментори'),

('addPdctH', 'en', 'Add a new mentor'),
('addPdctH', 'uk', 'Додати нового ментора'),

('addPBtn', 'en', 'add'),
('addPBtn', 'uk', 'додати'),

('pnameENInput', 'en', 'Enter the name in English'),
('pnameENInput', 'uk', 'Ведіть ім''я на англійській мові'),

('priceInput', 'en', 'Enter the price'),
('priceInput', 'uk', 'Ведіть ціну'),

('descENInput', 'en', 'Enter the description in English'),
('descENInput', 'uk', 'Ведіть опис на англійській мові'),

('imgInput', 'en', 'Attach image'),
('imgInput', 'uk', 'Прикрипіть зображення'),

('addPInvalidPrice', 'en', 'Invalid price input'),
('addPInvalidPrice', 'uk', 'Неправильно введена ціна'),

('addPSuccess', 'en', 'You have successfully added a mentor'),
('addPSuccess', 'uk', 'Ви успішно додали ментора'),

('pnameUAInput', 'en', 'Enter the name in Ukrainian'),
('pnameUAInput', 'uk', 'Ведіть ім''я на українській мові'),

('descUAInput', 'en', 'Enter the description in Ukrainian'),
('descUAInput', 'uk', 'Ведіть опис на українській мові'),

('addPTooHighPrice', 'en', 'Invalid numeric value for the price'),
('addPTooHighPrice', 'uk', 'Неправильне значення ціни'),

('priceHelper', 'en', 'e.g. 12.00$ per hour'),
('priceHelper', 'uk', 'Наприклад 12.00$ per hour'),

('amountOfHours', 'en', 'How many hours/sessions?'),
('amountOfHours', 'uk', 'Скільки годин/сессій?'),

('purchaseSuccess', 'en', 'You booked'),
('purchaseSuccess', 'uk', 'Ви забронювали'),

('forumBtn', 'en', 'forum'),
('forumBtn', 'uk', 'форум'),

('forumMsgDiv1', 'en', 'Write a message and talk to other users.'),
('forumMsgDiv1', 'uk', 'Пишіть повідомлення та розмовляйте з іншими користувачами.'),

('forumMsgHelper', 'en', 'Write your message...'),
('forumMsgHelper', 'uk', 'Напишіть повідомлення...'),

('forumNoMsgs', 'en', 'No messages yet.'),
('forumNoMsgs', 'uk', 'Повідомлень поки немає.'),

('purchaseFailUserNotLogged', 'en', 'Please log in to your account to order'),
('purchaseFailUserNotLogged', 'uk', 'Будь ласка, увійдіть в акаунт щоб замовляти'),

('purchaseFailAdmin', 'en', 'Admins cannot order'),
('purchaseFailAdmin', 'uk', 'Адміміністратори не може замовляти'),

('buyBtn', 'en', 'Buy'),
('buyBtn', 'uk', 'Придбати');