-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 01:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.6

drop database altmarket;
create database altmarket;
use altmarket;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


--
-- Database: `altmarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `code`, `name`, `is_active`) VALUES
(1, 'en', 'English', 1),
(2, 'fr', 'Français(French)', 1),
(3, 'ltz', 'Lëtzebuergesch(Luxembourgish)', 1),
(4, 'de', 'Deutsch(German)', 1),
(5, 'ro', 'Română(Romanian)', 1),
(6, 'pl', 'Polski(Polish)', 1),
(7, 'uk', 'Українська(Ukrainian)', 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `written_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `username`, `message`, `written_at`) VALUES
(1, 1, 'Admin', 'Welcome to our market\'s forum! Here you can give your feedback on our products. Remember, your opinion is very important, but is not really needed.', '2026-05-05 11:23:08');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` float NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `available` tinyint(4) NOT NULL DEFAULT 1,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `description`, `quantity`, `price`, `image_path`, `available`, `added_at`) VALUES
(1, 'Monster', 'White Monster.', 200, 1.2, 'assets/images/products/0e9b09a3a3893851d659103e78e5d375.png', 1, '2026-05-05 13:02:31'),
(2, 'Red Bull', 'Gives you wings.', 200, 2.3, 'assets/images/products/04006675fbc658c22629316dc456e586.jpeg', 1, '2026-05-05 13:03:20'),
(3, 'NON Stop', 'Slava Ukraini!', 200, 3, 'assets/images/products/d25b641438185fa62b8f99dd216fc561.png', 1, '2026-05-05 13:05:35'),
(4, 'Stranger', 'Strange man.', 50, 14.88, 'assets/images/products/60a86ce1e98216613e66244cd21cf45d.jpeg', 1, '2026-05-05 13:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` int(11) NOT NULL,
  `lang_code` varchar(5) NOT NULL,
  `key_name` varchar(255) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `translations`
--

INSERT INTO `translations` (`id`, `lang_code`, `key_name`, `value`) VALUES
(1, 'en', 'brand-name-1', 'Alt'),
(2, 'en', 'brand-name-2', 'Market'),
(3, 'en', 'tab-title', 'AltMarket: Home page'),
(4, 'en', 'navigation.nav-home', 'Home'),
(5, 'en', 'navigation.nav-market', 'Market'),
(6, 'en', 'navigation.nav-contact', 'Contact us'),
(7, 'en', 'nav-bar.sign-up', 'Sign up'),
(8, 'en', 'nav-bar.sign-in', 'Sign in'),
(9, 'en', 'footer.footer-title', '{brand}'),
(10, 'en', 'footer.footer-description', 'Web-shop of all sorts of energy drinks...'),
(11, 'en', 'footer.footer-go', 'Go to:'),
(12, 'en', 'footer.footer-social', 'Our social media:'),
(13, 'en', 'home-page.home-title', 'Welcome to the {brand}!'),
(14, 'en', 'home-page.home-description', 'The place where you can get all your favorite energy drinks and flavors.'),
(15, 'fr', 'brand-name-1', 'Alt'),
(16, 'fr', 'brand-name-2', 'Marché'),
(17, 'fr', 'tab-title', 'AltMarché: Accueil'),
(18, 'fr', 'navigation.nav-home', 'Accueil'),
(19, 'fr', 'navigation.nav-market', 'Marché'),
(20, 'fr', 'navigation.nav-contact', 'Contactez-nous'),
(21, 'fr', 'nav-bar.sign-up', 'S\'inscrire'),
(22, 'fr', 'nav-bar.sign-in', 'Se connecter'),
(23, 'fr', 'footer.footer-title', '{brand}'),
(24, 'fr', 'footer.footer-description', 'Boutique en ligne de toutes sortes de boissons énergétiques...'),
(25, 'fr', 'footer.footer-go', 'Aller à:'),
(26, 'fr', 'footer.footer-social', 'Nos réseaux sociaux:'),
(27, 'fr', 'home-page.home-title', 'Bienvenue sur l\'{brand}!'),
(28, 'fr', 'home-page.home-description', 'L\'endroit où vous pouvez retrouver toutes vos boissons énergétiques et saveurs préférées.'),
(29, 'ltz', 'brand-name-1', 'Alt'),
(30, 'ltz', 'brand-name-2', 'Maart'),
(31, 'ltz', 'tab-title', 'AltMaart: Haaptsäit'),
(32, 'ltz', 'navigation.nav-home', 'Haaptsäit'),
(33, 'ltz', 'navigation.nav-market', 'Maart'),
(34, 'ltz', 'navigation.nav-contact', 'Kontaktéiert eis'),
(35, 'ltz', 'nav-bar.sign-up', 'Umellen'),
(36, 'ltz', 'nav-bar.sign-in', 'Aloggen'),
(37, 'ltz', 'footer.footer-title', '{brand}'),
(38, 'ltz', 'footer.footer-description', 'Web-Shop fir all méiglech Energydrinks...'),
(39, 'ltz', 'footer.footer-go', 'Géi op:'),
(40, 'ltz', 'footer.footer-social', 'Eis sozial Medien:'),
(41, 'ltz', 'home-page.home-title', 'Wëllkomm bei {brand}!'),
(42, 'ltz', 'home-page.home-description', 'D\'Plaz wou s du all deng Liiblings-Energydrinks a Goûten fanne kanns.'),
(43, 'de', 'brand-name-1', 'Alt'),
(44, 'de', 'brand-name-2', 'Markt'),
(45, 'de', 'tab-title', 'AltMarkt: Startseite'),
(46, 'de', 'navigation.nav-home', 'Startseite'),
(47, 'de', 'navigation.nav-market', 'Markt'),
(48, 'de', 'navigation.nav-contact', 'Kontakt'),
(49, 'de', 'nav-bar.sign-up', 'Registrieren'),
(50, 'de', 'nav-bar.sign-in', 'Anmelden'),
(51, 'de', 'footer.footer-title', '{brand}'),
(52, 'de', 'footer.footer-description', 'Online-Shop für alle möglichen Energy-Drinks...'),
(53, 'de', 'footer.footer-go', 'Gehe zu:'),
(54, 'de', 'footer.footer-social', 'Unsere sozialen Medien:'),
(55, 'de', 'home-page.home-title', 'Willkommen bei {brand}!'),
(56, 'de', 'home-page.home-description', 'Der Ort, an dem du all deine Lieblings-Energydrinks und Geschmacksrichtungen findest.'),
(57, 'ro', 'brand-name-1', 'Alt'),
(58, 'ro', 'brand-name-2', 'Piață'),
(59, 'ro', 'tab-title', 'AltPiață: Pagina principală'),
(60, 'ro', 'navigation.nav-home', 'Acasă'),
(61, 'ro', 'navigation.nav-market', 'Piață'),
(62, 'ro', 'navigation.nav-contact', 'Contactează-ne'),
(63, 'ro', 'nav-bar.sign-up', 'Înregistrare'),
(64, 'ro', 'nav-bar.sign-in', 'Autentificare'),
(65, 'ro', 'footer.footer-title', '{brand}'),
(66, 'ro', 'footer.footer-description', 'Magazin online cu tot felul de băuturi energizante...'),
(67, 'ro', 'footer.footer-go', 'Mergi la:'),
(68, 'ro', 'footer.footer-social', 'Rețelele noastre sociale:'),
(69, 'ro', 'home-page.home-title', 'Bine ai venit la {brand}!'),
(70, 'ro', 'home-page.home-description', 'Locul unde găsești toate băuturile și aromele tale preferate.'),
(71, 'pl', 'brand-name-1', 'Alt'),
(72, 'pl', 'brand-name-2', 'Market'),
(73, 'pl', 'tab-title', 'AltMarket: Główna'),
(74, 'pl', 'navigation.nav-home', 'Główna'),
(75, 'pl', 'navigation.nav-market', 'Sklep'),
(76, 'pl', 'navigation.nav-contact', 'Kontakt'),
(77, 'pl', 'nav-bar.sign-up', 'Zarejestruj się'),
(78, 'pl', 'nav-bar.sign-in', 'Zaloguj się'),
(79, 'pl', 'footer.footer-title', '{brand}'),
(80, 'pl', 'footer.footer-description', 'Sklep internetowy z wszelkiego rodzaju napojami energetycznymi...'),
(81, 'pl', 'footer.footer-go', 'Przejdź do:'),
(82, 'pl', 'footer.footer-social', 'Nasze media społecznościowe:'),
(83, 'pl', 'home-page.home-title', 'Witamy w {brand}!'),
(84, 'pl', 'home-page.home-description', 'Miejsce, gdzie znajdziesz wszystkie swoje ulubione napoje energetyczne i smaki.'),
(85, 'uk', 'brand-name-1', 'Альт'),
(86, 'uk', 'brand-name-2', 'Маркет'),
(87, 'uk', 'tab-title', 'АльтМаркет: Головна'),
(88, 'uk', 'navigation.nav-home', 'Головна'),
(89, 'uk', 'navigation.nav-market', 'Магазин'),
(90, 'uk', 'navigation.nav-contact', 'Зв’яжіться з нами'),
(91, 'uk', 'nav-bar.sign-up', 'Зареєструватися'),
(92, 'uk', 'nav-bar.sign-in', 'Увійти'),
(93, 'uk', 'footer.footer-title', '{brand}'),
(94, 'uk', 'footer.footer-description', 'Інтернет-магазин усіх видів енергетичних напоїв...'),
(95, 'uk', 'footer.footer-go', 'Перейти до:'),
(96, 'uk', 'footer.footer-social', 'Ми в соцмережах:'),
(97, 'uk', 'home-page.home-title', 'Ласкаво просимо до {brand}у!'),
(98, 'uk', 'home-page.home-description', 'Місце, де можна знайти всі улюблені енергетичні напої та смаки.'),
(99, 'en', 'signing.username', 'Your username:'),
(100, 'en', 'signing.email', 'Your email:'),
(101, 'en', 'signing.password', 'Your password:'),
(102, 'en', 'signing.confirm', 'Confirm your password:'),
(103, 'en', 'signing.captcha', 'Solve: {n1} + {n2} = ?'),
(104, 'fr', 'signing.username', 'Votre nom d’utilisateur :'),
(105, 'fr', 'signing.email', 'Votre e-mail :'),
(106, 'fr', 'signing.password', 'Votre mot de passe :'),
(107, 'fr', 'signing.confirm', 'Confirmez le mot de passe :'),
(108, 'fr', 'signing.captcha', 'Résolvez : {n1} + {n2} = ?'),
(109, 'ltz', 'signing.username', 'Äre Benotzernumm:'),
(110, 'ltz', 'signing.email', 'Är E-Mail:'),
(111, 'ltz', 'signing.password', 'Äert Passwuert:'),
(112, 'ltz', 'signing.confirm', 'Passwuert bestätegen:'),
(113, 'ltz', 'signing.captcha', 'Léist: {n1} + {n2} = ?'),
(114, 'de', 'signing.username', 'Ihr Benutzername:'),
(115, 'de', 'signing.email', 'Ihre E-Mail:'),
(116, 'de', 'signing.password', 'Ihr Passwort:'),
(117, 'de', 'signing.confirm', 'Passwort bestätigen:'),
(118, 'de', 'signing.captcha', 'Lösen Sie: {n1} + {n2} = ?'),
(119, 'ro', 'signing.username', 'Numele dvs. de utilizator:'),
(120, 'ro', 'signing.email', 'Adresa dvs. de e-mail:'),
(121, 'ro', 'signing.password', 'Parola dvs.:'),
(122, 'ro', 'signing.confirm', 'Confirmați parola:'),
(123, 'ro', 'signing.captcha', 'Rezolvați: {n1} + {n2} = ?'),
(124, 'pl', 'signing.username', 'Twoja nazwa użytkownika:'),
(125, 'pl', 'signing.email', 'Twój e-mail:'),
(126, 'pl', 'signing.password', 'Twoje hasło:'),
(127, 'pl', 'signing.confirm', 'Potwierdź hasło:'),
(128, 'pl', 'signing.captcha', 'Rozwiąż: {n1} + {n2} = ?'),
(129, 'uk', 'signing.username', 'Ваш логін:'),
(130, 'uk', 'signing.email', 'Ваша електронна пошта:'),
(131, 'uk', 'signing.password', 'Ваш пароль:'),
(132, 'uk', 'signing.confirm', 'Підтвердіть пароль:'),
(133, 'uk', 'signing.captcha', 'Розв’яжіть: {n1} + {n2} = ?'),
(134, 'en', 'error.fill-all-fields', 'Fill all fields to procede!'),
(135, 'en', 'error.invalid-email', 'Invalid email!'),
(136, 'en', 'error.password-mismatch', 'Passwords don\'t match!'),
(137, 'en', 'error.short-password', 'Password is too short!'),
(138, 'en', 'error.captcha-incorrect', 'Incorrect captcha!');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `is_admin`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$WHc8M6Cr1k2.av7nSKkf8u5ufVTSjB4mPDc7zhaUfOBoSkXg67NyC', 1, '2026-05-05 11:16:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_user` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lang_code` (`lang_code`,`key_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
