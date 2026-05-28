<?php
// CommonCode.php
// One file included by ALL pages. It loads DB, auth, csrf, header/footer helpers.
// This prevents path problems and keeps the project consistent.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Absolute filesystem root of RPIF1 and includes directory.
define("PIF_ROOT", realpath(__DIR__));
define("PIF_APP", realpath(__DIR__ . "/../.."));
define("PIF_PUBLIC", realpath(PIF_APP . "/public"));

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/csrf.php";

if (!function_exists("normalizeWebPath")) {
    function normalizeWebPath($path) {
        $path = str_replace("\\", "/", (string)$path);
        if ($path === "" || $path === "/") {
            return "";
        }
        return "/" . trim($path, "/");
    }
}

if (!function_exists("webPrefixFor")) {
    function webPrefixFor($targetPath) {
        // This helper derives a browser-visible prefix from a filesystem path so
        // the same code can run under different local/VM Apache setups.
        $documentRoot = realpath($_SERVER["DOCUMENT_ROOT"] ?? "");
        $targetPath = realpath($targetPath);

        if (!$documentRoot || !$targetPath) {
            return "";
        }

        $documentRoot = rtrim(str_replace("\\", "/", $documentRoot), "/");
        $targetPath = str_replace("\\", "/", $targetPath);

        if (strpos($targetPath, $documentRoot) !== 0) {
            return "";
        }

        return normalizeWebPath(substr($targetPath, strlen($documentRoot)));
    }
}

if (!defined("PIF_APP_URL")) {
    define("PIF_APP_URL", webPrefixFor(PIF_APP));
}

if (!defined("PIF_PUBLIC_URL")) {
    define("PIF_PUBLIC_URL", webPrefixFor(PIF_PUBLIC));
}

if (!function_exists("appUrl")) {
    function appUrl($path = "") {
        $path = "/" . ltrim((string)$path, "/");
        return (PIF_APP_URL === "" ? "" : PIF_APP_URL) . $path;
    }
}

if (!function_exists("publicUrl")) {
    function publicUrl($path = "") {
        $path = "/" . ltrim((string)$path, "/");
        return (PIF_PUBLIC_URL === "" ? "" : PIF_PUBLIC_URL) . $path;
    }
}

if (!function_exists("hasThemeColumn")) {
    function hasThemeColumn($conn) {
        static $hasTheme = null;

        if ($hasTheme !== null) {
            return $hasTheme;
        }

        $sql = "
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'theme'
            LIMIT 1
        ";

        $res = mysqli_query($conn, $sql);
        $hasTheme = ($res && mysqli_fetch_assoc($res)) ? true : false;
        return $hasTheme;
    }
}

if (!function_exists("hasLanguageColumn")) {
    function hasLanguageColumn($conn) {
        static $hasLanguage = null;

        if ($hasLanguage !== null) {
            return $hasLanguage;
        }

        $sql = "
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'language'
            LIMIT 1
        ";

        $res = mysqli_query($conn, $sql);
        $hasLanguage = ($res && mysqli_fetch_assoc($res)) ? true : false;
        return $hasLanguage;
    }
}

if (!function_exists("hasTable")) {
    function hasTable($conn, $tableName) {
        static $tableCache = [];

        if (isset($tableCache[$tableName])) {
            return $tableCache[$tableName];
        }

        $sql = "
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            $tableCache[$tableName] = false;
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $tableName);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $tableCache[$tableName] = ($res && mysqli_fetch_assoc($res)) ? true : false;
        return $tableCache[$tableName];
    }
}

if (!function_exists("hasColumn")) {
    function hasColumn($conn, $tableName, $columnName) {
        static $columnCache = [];
        $cacheKey = $tableName . "." . $columnName;

        if (isset($columnCache[$cacheKey])) {
            return $columnCache[$cacheKey];
        }

        $sql = "
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            $columnCache[$cacheKey] = false;
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ss", $tableName, $columnName);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $columnCache[$cacheKey] = ($res && mysqli_fetch_assoc($res)) ? true : false;
        return $columnCache[$cacheKey];
    }
}

if (!function_exists("getThemePreference")) {
    function getThemePreference($conn) {
        $allowedThemes = ["light", "dark"];

        if (isset($_SESSION["theme"]) && in_array($_SESSION["theme"], $allowedThemes, true)) {
            return $_SESSION["theme"];
        }

        if (isset($_COOKIE["pif_theme"]) && in_array($_COOKIE["pif_theme"], $allowedThemes, true)) {
            return $_COOKIE["pif_theme"];
        }

        if (isset($_SESSION["user_id"]) && hasThemeColumn($conn)) {
            $stmt = mysqli_prepare($conn, "SELECT theme FROM users WHERE user_id = ?");
            if (!$stmt) {
                return "light";
            }
            mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $theme = $row["theme"] ?? "light";
                if (!in_array($theme, $allowedThemes, true)) {
                    $theme = "light";
                }

                $_SESSION["theme"] = $theme;
                setcookie("pif_theme", $theme, time() + (60 * 60 * 24 * 365), "/");
                return $theme;
            }
        }

        return "light";
    }
}

if (!function_exists("saveThemePreference")) {
    function saveThemePreference($conn, $theme) {
        $theme = ($theme === "dark") ? "dark" : "light";

        $_SESSION["theme"] = $theme;
        setcookie("pif_theme", $theme, time() + (60 * 60 * 24 * 365), "/");

        if (isset($_SESSION["user_id"]) && hasThemeColumn($conn)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET theme = ? WHERE user_id = ?");
            if (!$stmt) {
                return;
            }
            mysqli_stmt_bind_param($stmt, "si", $theme, $_SESSION["user_id"]);
            mysqli_stmt_execute($stmt);
        }
    }
}

if (!function_exists("getLanguagePreference")) {
    function getLanguagePreference($conn) {
        $allowedLanguages = ["en", "fr"];

        if (isset($_SESSION["language"]) && in_array($_SESSION["language"], $allowedLanguages, true)) {
            return $_SESSION["language"];
        }

        if (isset($_COOKIE["pif_language"]) && in_array($_COOKIE["pif_language"], $allowedLanguages, true)) {
            return $_COOKIE["pif_language"];
        }

        if (isset($_SESSION["user_id"]) && hasLanguageColumn($conn)) {
            $stmt = mysqli_prepare($conn, "SELECT language FROM users WHERE user_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $language = $row["language"] ?? "en";
                    if (!in_array($language, $allowedLanguages, true)) {
                        $language = "en";
                    }

                    $_SESSION["language"] = $language;
                    setcookie("pif_language", $language, time() + (60 * 60 * 24 * 365), "/");
                    return $language;
                }
            }
        }

        return "en";
    }
}

if (!function_exists("saveLanguagePreference")) {
    function saveLanguagePreference($conn, $language) {
        $language = ($language === "fr") ? "fr" : "en";

        $_SESSION["language"] = $language;
        setcookie("pif_language", $language, time() + (60 * 60 * 24 * 365), "/");

        if (isset($_SESSION["user_id"]) && hasLanguageColumn($conn)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET language = ? WHERE user_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $language, $_SESSION["user_id"]);
                mysqli_stmt_execute($stmt);
            }
        }
    }
}

if (!function_exists("esc")) {
    function esc($text) {
        return htmlspecialchars($text ?? "", ENT_QUOTES, "UTF-8");
    }
}

if (!function_exists("e")) {
    function e($text) {
        return esc($text);
    }
}

function toSqlDateTime($value) {
    $value = trim((string)$value);
    if ($value === "") {
        return "";
    }

    return str_replace("T", " ", $value) . ":00";
}

if (!function_exists("t")) {
    function t($key, $replacements = []) {
        static $messages = [
            "en" => [
                "login" => "Login",
                "register" => "Register",
                "logout" => "Logout",
                "welcome" => "Welcome",
                "stations" => "Stations",
                "measurements" => "Measurements",
                "collections" => "Collections",
                "friends" => "Friends",
                "chat" => "Chat",
                "account" => "Account",
                "admin" => "Admin",
                "dashboard" => "Dashboard",
                "users" => "Users",
                "language" => "Language",
                "appearance" => "Appearance",
                "current_language" => "Current language",
                "english" => "English",
                "french" => "French",
                "light_mode" => "Light mode",
                "dark_mode" => "Dark mode",
                "current_theme" => "Current theme",
                "signed_in_as" => "Signed in as :name",
                "my_account" => "My Account",
                "save" => "Save",
                "username" => "Username",
                "full_name" => "Full name",
                "email" => "Email",
                "password" => "Password",
                "confirm_password" => "Confirm Password",
                "new_password_optional" => "New password (optional)",
                "confirm_new_password" => "Confirm new password",
                "create_account" => "Create account",
                "wrong_username_or_password" => "Wrong username or password.",
                "please_fill_all_fields" => "Please fill all fields.",
                "passwords_do_not_match" => "Passwords do not match.",
                "account_created_login" => "Account created. You can login now.",
                "theme_updated" => "Theme updated.",
                "language_updated" => "Language updated.",
                "account_updated" => "Account updated.",
                "update_failed_exists" => "Update failed. Username/email may already exist.",
                "station_platform" => "Station platform",
                "your_station_workspace" => "Your station workspace",
                "workspace_intro" => "Monitor your registered stations, review incoming measurements, build collections, and share selected data with friends.",
                "my_stations" => "My stations",
                "account_overview" => "Account overview",
                "pending_friend_requests" => "Pending friend requests",
                "shared_with_me" => "Shared with me",
                "next_step" => "Next step",
                "review_activity" => "Review activity",
                "register_a_station" => "Register a station",
                "stations_linked" => "Stations linked to your account",
                "captured_from_your_stations" => "Captured from your stations",
                "saved_measurement_selections" => "Saved measurement selections",
                "people_you_can_share_with" => "People you can share with",
                "latest_station_activity" => "Latest station activity",
                "most_recent_reading" => "Most recent reading from each of your stations.",
                "manage_stations" => "Manage stations",
                "no_registered_stations" => "You have no registered stations yet. Add one by serial number to start receiving measurements.",
                "active" => "Active",
                "no_data" => "No data",
                "latest_reading" => "Latest reading",
                "temperature" => "Temperature",
                "humidity" => "Humidity",
                "pressure" => "Pressure",
                "recent_collections" => "Recent collections",
                "latest_saved_sets" => "Your latest saved measurement sets.",
                "open" => "Open",
                "no_collections_yet" => "No collections yet. Create one from your station measurements.",
                "no_description_added" => "No description added.",
                "rows" => "rows",
                "station" => "Station",
                "date_range" => "Date range",
                "sharing_and_requests" => "Sharing and requests",
                "current_collaboration_status" => "Current collaboration status.",
                "no_shared_collections" => "No collections have been shared with you yet.",
                "shared_by" => "Shared by :name",
                "owner" => "Owner",
                "view" => "View",
                "station_snapshot" => "Station snapshot",
                "latest_values_all_stations" => "Latest values across all of your stations.",
                "temperature_c" => "Temperature (C)",
                "humidity_percent" => "Humidity (%)",
                "no_chart_data" => "No measurement data is available yet for the chart.",
                "latest_measurement_trend" => "Latest measurement trend",
                "choose_station_chart" => "Select one of your stations to inspect its recent values.",
                "platform_measurement_volume" => "Platform measurement volume",
                "top_station_measurement_counts" => "Stations with the largest stored measurement sets.",
                "station_assignment_split" => "Station assignment split",
                "assignment_coverage_summary" => "Assigned versus currently available stations.",
                "admin_workspace" => "Admin workspace",
                "platform_control_center" => "Platform control center",
                "admin_intro" => "Manage users, station ownership, system-wide measurements, and all shared data structures from one place.",
                "system_snapshot" => "System snapshot",
                "current_state_platform" => "Current state of the platform.",
                "registered_users" => "Registered users",
                "assigned_stations" => "Assigned stations",
                "accounts_in_platform" => "Accounts in the platform",
                "registered_hardware_endpoints" => "Registered hardware endpoints",
                "stored_data_rows" => "Stored data rows",
                "recent_users" => "Recent users",
                "newest_accounts" => "Newest accounts in the system.",
                "manage" => "Manage",
                "no_users_available" => "No users available.",
                "user_id" => "User ID",
                "role" => "Role",
                "recent_stations" => "Recent stations",
                "latest_station_records" => "Latest station records and ownership state.",
                "no_stations_available" => "No stations available.",
                "serial" => "Serial",
                "available" => "Available",
                "assigned" => "Assigned",
                "no_owner" => "No owner",
            ],
            "fr" => [
                "login" => "Connexion",
                "register" => "Inscription",
                "logout" => "Déconnexion",
                "welcome" => "Accueil",
                "stations" => "Stations",
                "measurements" => "Mesures",
                "collections" => "Collections",
                "friends" => "Amis",
                "chat" => "Chat",
                "account" => "Compte",
                "admin" => "Admin",
                "dashboard" => "Tableau de bord",
                "users" => "Utilisateurs",
                "language" => "Langue",
                "appearance" => "Apparence",
                "current_language" => "Langue actuelle",
                "english" => "Anglais",
                "french" => "Français",
                "light_mode" => "Mode clair",
                "dark_mode" => "Mode sombre",
                "current_theme" => "Thème actuel",
                "signed_in_as" => "Connecté en tant que :name",
                "my_account" => "Mon compte",
                "save" => "Enregistrer",
                "username" => "Nom d'utilisateur",
                "full_name" => "Nom complet",
                "email" => "E-mail",
                "password" => "Mot de passe",
                "confirm_password" => "Confirmer le mot de passe",
                "new_password_optional" => "Nouveau mot de passe (optionnel)",
                "confirm_new_password" => "Confirmer le nouveau mot de passe",
                "create_account" => "Créer le compte",
                "wrong_username_or_password" => "Nom d'utilisateur ou mot de passe incorrect.",
                "please_fill_all_fields" => "Veuillez remplir tous les champs.",
                "passwords_do_not_match" => "Les mots de passe ne correspondent pas.",
                "account_created_login" => "Compte créé. Vous pouvez maintenant vous connecter.",
                "theme_updated" => "Thème mis à jour.",
                "language_updated" => "Langue mise à jour.",
                "account_updated" => "Compte mis à jour.",
                "update_failed_exists" => "Échec de la mise à jour. Le nom d'utilisateur ou l'e-mail existe peut-être déjà.",
                "station_platform" => "Plateforme de stations",
                "your_station_workspace" => "Votre espace stations",
                "workspace_intro" => "Surveillez vos stations enregistrées, consultez les mesures reçues, créez des collections et partagez des données sélectionnées avec vos amis.",
                "my_stations" => "Mes stations",
                "account_overview" => "Aperçu du compte",
                "pending_friend_requests" => "Demandes d'ami en attente",
                "shared_with_me" => "Partagé avec moi",
                "next_step" => "Prochaine étape",
                "review_activity" => "Consulter l'activité",
                "register_a_station" => "Enregistrer une station",
                "stations_linked" => "Stations liées à votre compte",
                "captured_from_your_stations" => "Capturées depuis vos stations",
                "saved_measurement_selections" => "Sélections de mesures enregistrées",
                "people_you_can_share_with" => "Personnes avec qui partager",
                "latest_station_activity" => "Dernière activité des stations",
                "most_recent_reading" => "Lecture la plus récente de chacune de vos stations.",
                "manage_stations" => "Gérer les stations",
                "no_registered_stations" => "Vous n'avez pas encore de stations enregistrées. Ajoutez-en une par numéro de série pour commencer à recevoir des mesures.",
                "active" => "Active",
                "no_data" => "Aucune donnée",
                "latest_reading" => "Dernière lecture",
                "temperature" => "Température",
                "humidity" => "Humidité",
                "pressure" => "Pression",
                "recent_collections" => "Collections récentes",
                "latest_saved_sets" => "Vos derniers ensembles de mesures enregistrés.",
                "open" => "Ouvrir",
                "no_collections_yet" => "Aucune collection pour le moment. Créez-en une à partir des mesures de vos stations.",
                "no_description_added" => "Aucune description ajoutée.",
                "rows" => "lignes",
                "station" => "Station",
                "date_range" => "Période",
                "sharing_and_requests" => "Partages et demandes",
                "current_collaboration_status" => "État actuel de la collaboration.",
                "no_shared_collections" => "Aucune collection ne vous a encore été partagée.",
                "shared_by" => "Partagé par :name",
                "owner" => "Propriétaire",
                "view" => "Voir",
                "station_snapshot" => "Vue des stations",
                "latest_values_all_stations" => "Dernieres valeurs sur toutes vos stations.",
                "temperature_c" => "Temperature (C)",
                "humidity_percent" => "Humidite (%)",
                "no_chart_data" => "Aucune mesure n'est encore disponible pour le graphique.",
                "latest_measurement_trend" => "Tendance recente des mesures",
                "choose_station_chart" => "Selectionnez une de vos stations pour consulter ses dernieres valeurs.",
                "platform_measurement_volume" => "Volume des mesures de la plateforme",
                "top_station_measurement_counts" => "Stations avec les plus grands ensembles de mesures stockes.",
                "station_assignment_split" => "Repartition des stations",
                "assignment_coverage_summary" => "Stations attribuees par rapport aux stations disponibles.",
                "admin_workspace" => "Espace administrateur",
                "platform_control_center" => "Centre de contrôle de la plateforme",
                "admin_intro" => "Gérez les utilisateurs, l'attribution des stations, les mesures du système et toutes les structures de partage depuis un seul endroit.",
                "system_snapshot" => "Aperçu du système",
                "current_state_platform" => "État actuel de la plateforme.",
                "registered_users" => "Utilisateurs enregistrés",
                "assigned_stations" => "Stations attribuées",
                "accounts_in_platform" => "Comptes dans la plateforme",
                "registered_hardware_endpoints" => "Équipements enregistrés",
                "stored_data_rows" => "Lignes de données stockées",
                "recent_users" => "Utilisateurs récents",
                "newest_accounts" => "Comptes les plus récents du système.",
                "manage" => "Gérer",
                "no_users_available" => "Aucun utilisateur disponible.",
                "user_id" => "ID utilisateur",
                "role" => "Rôle",
                "recent_stations" => "Stations récentes",
                "latest_station_records" => "Derniers enregistrements de stations et état d'attribution.",
                "no_stations_available" => "Aucune station disponible.",
                "serial" => "Numéro de série",
                "available" => "Disponible",
                "assigned" => "Attribuée",
                "no_owner" => "Aucun propriétaire",
            ],
        ];

        global $conn;
        $language = isset($conn) ? getLanguagePreference($conn) : "en";
        $text = $messages[$language][$key] ?? ($messages["en"][$key] ?? $key);

        foreach ($replacements as $name => $value) {
            $text = str_replace(":" . $name, (string)$value, $text);
        }

        $text = strtr($text, [
            "Ã©" => "e",
            "Ã¨" => "e",
            "Ãª" => "e",
            "Ã«" => "e",
            "Ã " => "a",
            "Ã¢" => "a",
            "Ã§" => "c",
            "Ã´" => "o",
            "Ã¹" => "u",
            "Ã»" => "u",
            "Ã®" => "i",
            "Ã¯" => "i",
            "Ã‰" => "E",
        ]);

        return $text;
    }
}

// Return current user info from database (used in account.php)
function getCurrentUser($conn) {
    if (!isset($_SESSION["user_id"])) {
        return null;
    }

    $id = (int)$_SESSION["user_id"];

    if (hasThemeColumn($conn) && hasLanguageColumn($conn)) {
        $sql = "SELECT user_id, username, full_name, email, role, theme, language FROM users WHERE user_id=?";
    } else if (hasThemeColumn($conn)) {
        $sql = "SELECT user_id, username, full_name, email, role, theme FROM users WHERE user_id=?";
    } else if (hasLanguageColumn($conn)) {
        $sql = "SELECT user_id, username, full_name, email, role, language FROM users WHERE user_id=?";
    } else {
        $sql = "SELECT user_id, username, full_name, email, role FROM users WHERE user_id=?";
    }

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($res); // returns array or null
}
