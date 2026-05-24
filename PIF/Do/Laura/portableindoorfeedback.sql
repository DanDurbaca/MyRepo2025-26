-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 08, 2025 at 08:25 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Project_Database`
--
CREATE DATABASE IF NOT EXISTS `Project_Database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `Project_Database`;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `pk_collection` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `fk_user_creates` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `pkfk_collection` int NOT NULL,
  `pkfk_measurement` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasaccess`
--

CREATE TABLE `hasaccess` (
  `pkfk_user` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_collection` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `isfriend`
--

CREATE TABLE `isfriend` (
  `pkfk_user_user` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `pkfk_user_friend` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `pk_measurement` int NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `humidity` decimal(5,2) NOT NULL,
  `pressure` decimal(6,2) NOT NULL,
  `light` decimal(6,2) NOT NULL,
  `gas` decimal(6,2) NOT NULL,
  `timestamp` datetime NOT NULL,
  `fk_station_records` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `station`
--

CREATE TABLE `station` (
  `pk_serialNumber` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `fk_user_owns` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `pk_username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('User','Admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`pk_collection`),
  ADD KEY `fkc_user_creates_collection` (`fk_user_creates`);

--
-- Indexes for table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`pkfk_collection`,`pkfk_measurement`),
  ADD KEY `fkc_contains_measurement` (`pkfk_measurement`);

--
-- Indexes for table `hasaccess`
--
ALTER TABLE `hasaccess`
  ADD PRIMARY KEY (`pkfk_user`,`pkfk_collection`),
  ADD KEY `fkc_hasaccess_collection` (`pkfk_collection`);

--
-- Indexes for table `isfriend`
--
ALTER TABLE `isfriend`
  ADD PRIMARY KEY (`pkfk_user_user`,`pkfk_user_friend`),
  ADD KEY `fkc_isfriend_friend` (`pkfk_user_friend`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`pk_measurement`),
  ADD KEY `fkc_station_records_measurement` (`fk_station_records`);

--
-- Indexes for table `station`
--
ALTER TABLE `station`
  ADD PRIMARY KEY (`pk_serialNumber`),
  ADD KEY `fkc_user_owns_station` (`fk_user_owns`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`pk_username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `pk_collection` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `pk_measurement` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `fkc_user_creates_collection` FOREIGN KEY (`fk_user_creates`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `fkc_contains_collection` FOREIGN KEY (`pkfk_collection`) REFERENCES `collection` (`pk_collection`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkc_contains_measurement` FOREIGN KEY (`pkfk_measurement`) REFERENCES `measurement` (`pk_measurement`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hasaccess`
--
ALTER TABLE `hasaccess`
  ADD CONSTRAINT `fkc_hasaccess_collection` FOREIGN KEY (`pkfk_collection`) REFERENCES `collection` (`pk_collection`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkc_hasaccess_user` FOREIGN KEY (`pkfk_user`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `isfriend`
--
ALTER TABLE `isfriend`
  ADD CONSTRAINT `fkc_isfriend_friend` FOREIGN KEY (`pkfk_user_friend`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkc_isfriend_user` FOREIGN KEY (`pkfk_user_user`) REFERENCES `user` (`pk_username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `measurement`
--
ALTER TABLE `measurement`
  ADD CONSTRAINT `fkc_station_records_measurement` FOREIGN KEY (`fk_station_records`) REFERENCES `station` (`pk_serialNumber`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `station`
--
ALTER TABLE `station`
  ADD CONSTRAINT `fkc_user_owns_station` FOREIGN KEY (`fk_user_owns`) REFERENCES `user` (`pk_username`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE LanguageSwitch (
    NameCalled varchar(255),
    EnglishVersion varchar(255),
    FrenchVersion varchar(255)
);


Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Welcomeall", "Welcome", "Bienvenue");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserUnknown", "User Unknown", "Utilisateur inconnu");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Login", "Login", "Se connecter");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Logout", "Logout", "Se déconnecter");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Admin", "Administrator Page", "Page administrateur");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Register", "Register", "Registre");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("language", "En Français", "In English");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginForm", "Please enter your username and password here", "Veuillez entrer le nom d'utilisateur et votre mot de passe ici");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginFormUser", "Enter your username", "Entrez le nom d'utilisateur");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginFormPass", "Enter your password", "Entrez le mot de passe");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginCorrect", "Your password is correct", "Votre mot de passe est correct");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginInvalid", "Invalid password", "Mot de passe invalide");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginDatabase", "Your username is not in our database", "Votre nom d'utilisateur n'est pas dans notre base de données");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterFrom", "To Register, please fill in the following form:", "Pour vous inscrire, veuillez remplir le formulaire suivant :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterFormUser", "Enter your username", "Entrez un nom d'utilisateur");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterFormPass", "Please choose a password", "Veuillez choisir un mot de passe");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterFormPass2", "Please retype the password", "Veuillez retaper le mot de passe");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterBotton", "Create account", "Créer un compte");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterRegistration", "Registration in process...", "Inscription en cours...");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterExists", "User already exists, pick another one", "L'utilisateur existe déjà, choisissez-en un autre");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterSuccesfully", "Registration successfully", "Inscription réussie");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterPassMatch", "Password do not match. Please try again!", "Le mot de passe ne correspond pas. Veuillez réessayer !");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ChangePasswordTitle", "Change Your Password", "Changez votre mot de passe" );
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("OldPassword", "Current Password", "Mot de passe actuel");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NewPassword", "New Password", "Nouveau mot de passe");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ConfirmNewPassword", "Confirm New Password", "Confirmer le nouveau mot de passe");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ChangePasswordButton", "Change Password", "Changer le mot de passe" );
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("PasswordsDoNotMatch", "New passwords do not match. ❌", "Les nouveaux mots de passe ne correspondent pas. ❌");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("OldPasswordWrong", "Current password is incorrect. ❌", "Le mot de passe actuel est incorrect. ❌");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("PasswordChangedSuccess", "Password successfully changed. ✅", "Mot de passe changé. ✅");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Email", "Email", "Email");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("FirstName", "First name", "Prénom");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LastName", "Last name", "Nom de famille");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NewEmail", "New Email", "Nouvel email");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NewFirstName", "New First Name", "Nouveau prénom");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NewLastName", "New Last Name", "Nouveau nom de famille");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("newUsername", "New Username", "Nouveau nom d'utilisateur");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Menu", "Menu", "Menu");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginNoAccount", "Don't have an account? ", "Vous n'avez pas de compte ?");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterHere", "Register here", "Inscrivez-vous ici");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LoginPage", "Go to Login Page", "Aller à la page de connexion");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserOnly", "This page is for registered users only. Please log in to access the menu.", "Cette page est réservée aux utilisateurs enregistrés. Veuillez vous connecter pour accéder au menu.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Welcome", "Welcome to the Portable Indoor Feedback Station", "Bienvenue chez Portable Indoor Feedback Station");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("FriendPage", "Friend Page", "Ajoute des amis");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Collection", "Collection Page", "Page de collection");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Station", "Station Page", "Page de la station");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Measurement", "Measurement Page", "Page de mesure");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ResearchText", "Research an username to add as a friend:", "Recherchez un nom d'utilisateur à ajouter en tant qu'ami :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SendFriendRequest", "Send Friend Request", "Envoyer une demande d'ami");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CurrentFriends", "Your Current Friends:", "Vos amis actuels :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CurrentFriendRequests", "Your Current Friend Requests:", "Vos demandes d'amis en cours :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Accept", "Accept", "Accepter");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Decline", "Decline", "Décliner");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoFriends", "You have no friends added yet.", "Vous n'avez pas encore ajouté d'amis.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoFriendRequests", "You have no friend requests at the moment.", "Vous n'avez pas de demandes d'amis pour le moment.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("EnterUsername", "Enter Username", "Entrez le nom d'utilisateur");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ResearchButton", "Research", "Rechercher");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("FriendExists", "Friend request sent successfully.", "Demande d'ami envoyée avec succès.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SearchResult", "Search Results:", "Résultats de la recherche :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("FriendRequests", "Friend Requests", "Demandes d'amis");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RemoveFriend", "End Friendship", "Mettre fin à l'amitié");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("YourStations", "Your Stations:", "Vos stations :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoStations", "No Stations.", "Aucune station.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("RegisterStation", "Register unassigned Station:", "Enregistrer une station non attribuée :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateStation", "Create New Station:", "Créer une nouvelle station :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Register", "Register", "Enregistrer");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Create", "Create", "Créer");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Available", "Available:", "Disponible :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SearchResults", "Search Results:", "Résultats de la recherche :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Measurements", "Measurements", "Mesures");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Explenation1", "This platform allows you to view measurements collected by your stations.", "Vous pouvez gérer vos stations, consulter les données des capteurs et organiser les mesures en collections.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Explenation2", "You can manage your stations, view sensor data, and organize measurements into collections.", "Vous pouvez gérer vos stations, consulter les données des capteurs et organiser les mesures en collections.");


Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationName", "Name (optional)", "Nom (optionnel)");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationDescription", "Description (optional)", "Description (optionnel)");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SerialNumber", "Serial Number:", "Numéro de série:");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationOwner", "Station Owner (optional)", "Propriétaire de la station (optionnel)");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateStation", "Create Station", "Créer une station");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("MessageStationCreated", "Station created.", "Station créée.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("MessageStationFailed", "Could not create station (maybe duplicate serial).", "Impossible de créer la station (peut-être un numéro de série en double).");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("MessageStationRequired", "Serial Number is required.", "Le numéro de série est requis.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateButton", "Create", "Créer");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationInstructions", "To create a new station, please fill in the following form:", "Pour créer une nouvelle station, veuillez remplir le formulaire suivant :");


Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Management", "Management", "Gestion");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Stations", "Stations", "Stations");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AddStations", "Add Stations", "Ajouter des stations");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ManageStations", "Manage Stations", "Gérer les stations");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Users", "Users", "Utilisateurs");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ManageUsers", "Manage Users", "Gérer les utilisateurs");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionsTitle", "Collections", "Collections");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateNewCollection", "Create a new collection", "Créer une nouvelle collection");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("MyCollectionsTitle", "My Collections", "Mes collections");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ViewMyCollections", "View collections you created", "Voir les collections que vous avez créées");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SharedCollectionsTitle", "Shared Collections", "Collections partagées");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ViewSharedCollections", "View collections shared with you", "Voir les collections partagées avec vous");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateCollection", "Create Collection", "Créer une collection");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("EndDateAfterStart", "End date must be after start date.", "La date de fin doit être postérieure à la date de début.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NotYourStation", "You can only create collections from your own stations.", "Vous ne pouvez créer des collections qu'à partir de vos propres stations.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionCreatedPrefix", "Collection created with ", "Collection créée avec ");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionCreatedSuffix", " measurements.", " mesures.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NeedAtLeastOneStation", "You need at least one station to create a collection.", "Vous devez avoir au moins une station pour créer une collection.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("GoToStations", "Go to Stations", "Aller aux stations");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationLabel", "Station:", "Station :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SelectStationOption", "-- Select Station --", "-- Sélectionner une station --");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Unnamed", "Unnamed", "Sans nom");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionNameLabel", "Collection Name:", "Nom de la collection :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionDescLabel", "Description (optional):", "Description (optionnelle) :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StartDateTime", "Start Date/Time:", "Date/heure de début :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("EndDateTime", "End Date/Time:", "Date/heure de fin :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Cancel", "Cancel", "Annuler");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationDeleted", "Station deleted.", "Station supprimée.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationDeleteFailed", "Could not delete station.", "Impossible de supprimer la station.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserDoesNotExist", "User does not exist.", "L'utilisateur n'existe pas.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationUpdated", "Station updated.", "Station mise à jour.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationUpdateFailed", "Could not update station.", "Impossible de mettre à jour la station.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableSerial", "Serial", "Numéro de série");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableName", "Name", "Nom");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableDescription", "Description", "Description");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableOwner", "Owner", "Propriétaire");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableActions", "Actions", "Actions");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Update", "Update", "Mettre à jour");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Delete", "Delete", "Supprimer");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoCollectionsYet", "You have no collections yet.", "Vous n'avez pas encore de collections.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("View", "View", "Voir");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Rename", "Rename", "Renommer");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ConfirmDeleteCollection", "Delete this collection?", "Supprimer cette collection ?");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionsSharedWithMe", "Collections Shared With Me", "Collections partagées avec moi");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SharedCollectionsDescription", "These collections were created by other users and shared with you. You can view them but cannot edit or delete them.", "Ces collections ont été créées par d'autres utilisateurs et partagées avec vous. Vous pouvez les consulter mais pas les modifier ou les supprimer.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoCollectionsShared", "No collections have been shared with you.", "Aucune collection ne vous a été partagée.");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserManagementTitle", "User Management", "Gestion des utilisateurs");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CannotDeleteOwnAccount", "Cannot delete your own account.", "Impossible de supprimer votre propre compte.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserDeleted", "User deleted.", "Utilisateur supprimé.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserDeleteFailed", "Could not delete user.", "Impossible de supprimer l'utilisateur.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserUpdated", "User updated.", "Utilisateur mis à jour.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("UserUpdateFailed", "Could not update user.", "Impossible de mettre à jour l'utilisateur.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableUsername", "Username", "Nom d'utilisateur");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableFirstName", "First Name", "Prénom");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableLastName", "Last Name", "Nom de famille");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableEmail", "Email", "Email");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TableRole", "Role", "Rôle");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("TablePassword", "Password", "Mot de passe");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LeaveEmptyToKeep", "Leave empty to keep", "Laisser vide pour conserver");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("InvalidCollectionID", "Invalid collection ID.", "ID de collection invalide.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AccessDenied", "Access denied.", "Accès refusé.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoPermissionViewCollection", "You do not have permission to view this collection.", "Vous n'êtes pas autorisé à consulter cette collection.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoMeasurementsInCollection", "No measurements in this collection.", "Aucune mesure dans cette collection.");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Timestamp", "Timestamp", "Marqueur temporel");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Station", "Station", "Station");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Temperature", "Temperature", "Température");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Humidity", "Humidity", "Humidité");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Pressure", "Pressure", "Pression");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Light", "Light", "Luminosité");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Gas", "Gas", "Gaz");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Collections", "Collections", "Collections");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ManageCollections", "Manage Collections", "Gérer les collections");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AddMeasurementsToCollection", "Add Measurements to Collection", "Ajouter des mesures à la collection");

-- New entries for Admin pages
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AdminAllCollections", "Admin - All Collections", "Admin - Toutes les collections");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoCollectionsFound", "No collections found.", "Aucune collection trouvée.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreatedBy", "Created By", "Créé par");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AdminCreateCollection", "Admin - Create Collection", "Admin - Créer une collection");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("EndDateAfterStartError", "End date must be after start date.", "La date de fin doit être postérieure à la date de début.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionCreatedWith", "Collection created with ", "Collection créée avec ");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("MeasurementsSuffix", " measurements.", " mesures.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionCreationFailed", "Collection creation failed.", "Échec de la création de la collection.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateAnother", "Create Another", "Créer un autre");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ViewCollections", "View Collections", "Voir les collections");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("BackToAdmin", "Back to Admin", "Retour à Admin");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoStationsAvailable", "No stations available in the database.", "Aucune station disponible dans la base de données.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StationColon", "Station:", "Station :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SelectStationDots", "Select station...", "Sélectionner une station...");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionNameColon", "Collection Name:", "Nom de la collection :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CollectionDescriptionColon", "Collection Description:", "Description de la collection :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("StartDateTimeColon", "Start Date/Time:", "Date/heure de début :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("EndDateTimeColon", "End Date/Time:", "Date/heure de fin :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CreateCollectionButton", "Create Collection", "Créer une collection");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("MeasurementsTitle", "Measurements", "Mesures");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AllStations", "All stations", "Toutes les stations");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("AllMyStations", "All my stations", "Toutes mes stations");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("DateRangeLabel", "Date Range:", "Les dates :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ChooseDateRange", "Choose a date range", "Choisissez les dates");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Today", "Today", "Aujourd'hui");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Last24Hours", "Last 24 hours", "Dernières 24 heures");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Last7Days", "Last 7 days", "7 derniers jours");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Apply", "Apply", "Appliquer");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SelectStationOrRange", "Please select a station or date range and click Apply to view measurements.", "Veuillez sélectionner une station ou une plage de dates et cliquer sur Appliquer pour afficher les mesures.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoMeasurementsFound", "No measurements found.", "Aucune mesure trouvée.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("LatestMeasurements", "Latest measurements", "Dernières mesures");

Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("ShareWithFriends", "Share with friends:", "Partager avec des amis :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("SelectFriendDots", "Select friend...", "Sélectionner un ami...");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Share", "Share", "Partager");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NoFriendsToShare", "No friends to share with.", "Aucun ami avec qui partager.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("CurrentlySharedWith", "Currently shared with:", "Actuellement partagé avec :");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("NotSharedWithAnyone", "Not shared with anyone.", "Partagé avec personne.");
Insert Into LanguageSwitch (NameCalled, EnglishVersion, FrenchVersion) Values("Unshare", "Unshare", "Ne plus partager");