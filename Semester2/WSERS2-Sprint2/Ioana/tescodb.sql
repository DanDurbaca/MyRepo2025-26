-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 12:34 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tescodb`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `userID` int(11) NOT NULL,
  `Username` varchar(30) DEFAULT NULL,
  `userPassword` varchar(200) DEFAULT NULL,
  `userTypes` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`userID`, `Username`, `userPassword`, `userTypes`) VALUES
(5, 'jonzzz', '$2y$10$7m2KWl0UP9EO.GIPnK1Xnecxj3jy1ZMqctltHrjGsjJdMXcGFskgS', 'regular_customer'),
(6, 'stel', '$2y$10$zP5g3FrDO.VN1lgAFhPL3.UqHSun3x4fbTnfPs9hbulM/uogWcqw2', 'administrator');

-- --------------------------------------------------------

--
-- Table structure for table `forum`
--

CREATE TABLE `forum` (
  `postID` int(11) NOT NULL,
  `postUsername` varchar(30) DEFAULT NULL,
  `postMessage` varchar(500) DEFAULT NULL,
  `postDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum`
--

INSERT INTO `forum` (`postID`, `postUsername`, `postMessage`, `postDate`) VALUES
(1, 'jonzzz', 'Hello :)', '2026-05-04 20:41:30');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name_en` varchar(30) DEFAULT NULL,
  `image_link` varchar(30) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `description_en` varchar(200) DEFAULT NULL,
  `product_name_gr` varchar(30) DEFAULT NULL,
  `description_gr` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name_en`, `image_link`, `price`, `description_en`, `product_name_gr`, `description_gr`) VALUES
(1, 'Smoked Salmon', 'Images/salmon.png', 6, 'Smoked salmon slices, defrosted', 'Καπνιστός Σολομός', 'Φέτες καπνιστού σολομού, αποψυγμένες'),
(2, 'Sourdough Bread', 'Images/bread.png', 2, 'It\'s the original for a reason-just simple, high quality ingredients for that perfect, tangy flavour and delicious chewy texture of a great sourdough.', 'Ψωμί Προζυμιού', 'Είναι το αυθεντικό για έναν λόγο - απλά υψηλής ποιότητας συστατικά για την τέλεια, πικάντικη γεύση και την υπέροχη μασώμενη υφή ενός εξαιρετικού ψωμιού προζυμιού.'),
(3, 'Pack of butter', 'Images/butter.png', 2, '100% British Milk Expertly churned for a smooth creamy taste.', 'Πακέτο Βούτυρο', '100% Βρετανικό Γάλα Expertly churned για μια απαλή κρεμώδη γεύση');

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `TranslationsID` int(11) NOT NULL,
  `myKey` varchar(30) DEFAULT NULL,
  `english` varchar(100) DEFAULT NULL,
  `greek` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `translations`
--

INSERT INTO `translations` (`TranslationsID`, `myKey`, `english`, `greek`) VALUES
(1, 'nav_home', 'Home', 'Αρχική'),
(2, 'nav_contact', 'Contact', 'Επικοινωνία'),
(3, 'nav_products', 'Products', 'Προϊόντα'),
(4, 'nav_register', 'Register', 'Εγγραφή'),
(5, 'home_title', 'Every little helps', 'Κάθε λίγο βοηθάει'),
(6, 'contact_title', 'Contact Us', 'Επικοινωνήστε μαζί μας'),
(7, 'contact_info_text', 'This is our contact information...', 'Αυτές είναι οι πληροφορίες επικοινωνίας μας...'),
(8, 'contact_email', 'Email', 'Email'),
(9, 'contact_phone', 'Phone', 'Τηλέφωνο'),
(10, 'contact_address', 'Address', 'Διεύθυνση'),
(11, 'contact_fname', 'First name:', 'Όνομα:'),
(12, 'contact_password', 'Your password:', 'Ο κωδικός σας:'),
(13, 'contact_submit', 'Send this data', 'Αποστολή δεδομένων'),
(14, 'contact_welcome', 'Welcome to our website', 'Καλώς ήρθατε στην ιστοσελίδα μας'),
(15, 'register_title', 'Registration form:', 'Φόρμα εγγραφής:'),
(16, 'register_processing', 'Registration in process', 'Η εγγραφή είναι σε εξέλιξη'),
(17, 'register_passwords_match', 'Passwords match and valid username. You will be registered...', 'Οι κωδικοί ταιριάζουν. Θα εγγραφείτε...'),
(18, 'register_passwords_error', 'Error. The two passwords do not match or user already exists. Please try again!', 'Σφάλμα. Οι δύο κωδικοί δεν ταιριάζουν. Παρακαλώ δοκιμάστε ξανά!'),
(19, 'register_username', 'Pick a username:', 'Επιλέξτε όνομα χρήστη:'),
(20, 'register_password', 'Pick a password', 'Επιλέξτε κωδικό πρόσβασης'),
(21, 'register_password_again', 'Confirm password', 'Επιβεβαιώστε τον κωδικό'),
(22, 'register_submit', 'Register', 'Εγγραφή'),
(23, 'login_title', 'Login', 'Σύνδεση'),
(24, 'login_username', 'Username', 'Όνομα χρήστη:'),
(25, 'login_password', 'Password', 'Κωδικός πρόσβασης:'),
(26, 'login_submit', 'Login', 'Σύνδεση'),
(27, 'login_error', 'Invalid username or password!', 'Λάθος όνομα χρήστη ή κωδικός!'),
(28, 'login_success', 'Login successful! Welcome', 'Επιτυχής σύνδεση! Καλώς ήρθατε'),
(29, 'nav_login', 'Login', 'Σύνδεση'),
(30, 'nav_admin', 'Admin', 'Διαχειρ'),
(31, 'logout', 'Logout', 'Αποσύνδεση'),
(32, 'currency_symbol', '£', '£'),
(33, 'HomeBtn', 'Home', 'Σπίτι'),
(34, 'ContactBtn', 'Contact', 'Επαφή'),
(35, 'ProductBtn', 'Products', 'Προϊόντα'),
(36, 'RegisterBtn', 'Register', 'Register'),
(37, 'LoginBtn', 'Login', 'Login'),
(38, 'AdminBtn', 'Admin', 'Διαχειρ'),
(39, 'ForumBtn', 'Forum', 'Φόρουμ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`postID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`TranslationsID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `forum`
--
ALTER TABLE `forum`
  MODIFY `postID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `TranslationsID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `clients` (`userID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
