<?php
// CommonCode.php for PIF
session_start();

// Connection to DB
$host = '';
$db   = 'portableindoorfeedback';
$user = 'root';
$pass = '';
$conn = mysqli_connect($host, $user, $pass, $db);
if ($conn) {
    mysqli_set_charset($conn, 'utf8mb4');
}

// ===== Translations (EN / UK / LB) =====
$TRANSLATIONS = [
    'en' => [
        'dashboard'         => 'Dashboard',
        'stations'          => 'Stations',
        'measurements'      => 'Measurements',
        'collections'       => 'Collections',
        'my_collections'    => 'My Collections',
        'shared_with_me'    => 'Shared with Me',
        'friends'           => 'Friends',
        'chat'              => 'Chat',
        'notifications'     => 'Notifications',
        'admin'             => 'Admin Panel',
        'account'           => 'Account',
        'logout'            => 'Logout',
        'login'             => 'Login',
        'register'          => 'Register',
        'welcome_back'      => 'Welcome back',
        'overview'          => 'Indoor climate monitoring overview',
        'no_stations'       => 'You have no stations yet.',
        'no_data'           => 'No data yet.',
        'save'              => 'Save',
        'save_changes'      => 'Save Changes',
        'delete'            => 'Delete',
        'cancel'            => 'Cancel',
        'send'              => 'Send',
        'edit'              => 'Edit',
        'view'              => 'View',
        'open'              => 'Open',
        'temperature'       => 'Temperature',
        'humidity'          => 'Humidity',
        'pressure'          => 'Pressure',
        'light'             => 'Light',
        'gas'               => 'Air Quality',
        'last_updated'      => 'Last updated',
        'last_24h'          => 'Last 24h',
        'from'              => 'From',
        'to'                => 'To',
        'show'              => 'Show',
        'name'              => 'Name',
        'description'       => 'Description',
        'username'          => 'Username',
        'email'             => 'Email',
        'password'          => 'Password',
        'first_name'        => 'First Name',
        'last_name'         => 'Last Name',
        'role'              => 'Role',
        'theme'             => 'Theme',
        'language'          => 'Language',
        'dark'              => 'Dark',
        'theme_light'       => 'Light',
        'friend_requests'   => 'Friend Requests',
        'add_friend'        => 'Add Friend',
        'remove_friend'     => 'Remove Friend',
        'accept'            => 'Accept',
        'decline'           => 'Decline',
        'sent_requests'     => 'Sent Requests',
        'incoming_requests' => 'Incoming Requests',
        'invite_link'       => 'Invite Link',
        'generate_invite'   => 'Generate Invite Link',
        'invite_desc'       => 'Generate a one-time link to invite someone new to PIF (valid 7 days).',
        'used_by'           => 'Used by',
        'pending'           => 'Pending',
        'share_collection'  => 'Share Collection',
        'create_collection' => 'Create Collection',
        'register_station'  => 'Register Station',
        'serial_number'     => 'Serial number',
        'station'           => 'Station',
        'station_name'      => 'Station name',
        'edit_station'      => 'Edit Station',
        'edit_collection'   => 'Edit Collection',
        'station_measurements' => 'Station Measurements',
        'chart_view'        => 'Chart view',
        'table_view'        => 'Table view',
        'select_metric'     => 'Select metric',
        'records'           => 'records',
        'all'               => 'All',
        'none'              => 'None',
        'no_collections'    => 'You have no collections yet.',
        'no_shared'         => 'No collections shared with you yet.',
        'no_friends'        => 'No friends yet.',
        'shared_with'       => 'Shared with',
        'measurements_unit' => 'measurements',
        'creator'           => 'Creator',
        'no_measurements'   => 'No measurements found for the selected period.',
        'send_request'      => 'Send Request',
        'view_data'         => 'View Data',
        'mark_all_read'     => 'Mark all read',
        'no_notifications'  => 'No notifications.',
        'type_message'      => 'Type a message...',
        'select_friend'     => 'Select a friend to chat',
        'no_messages_yet'   => 'No messages yet. Say hi!',
        'add_friends_first' => 'Add friends first to start chatting.',
        'go_to_friends'     => 'Go to Friends',
        'new_password'      => 'New Password',
        'current_password'  => 'Current Password',
        'confirm_password'  => 'Confirm Password',
        'leave_pwd_blank'   => 'Leave empty to keep current password',
        'cannot_be_changed' => '(cannot be changed)',
        'account_desc'      => 'Manage your profile, password and preferences.',
        'stations_desc'     => 'Register and manage your weather stations. Enter the serial number to claim a station.',
        'measurements_desc' => 'Filter and visualize sensor data. Switch between table and chart view.',
        'collections_desc'  => 'Group your measurements into named collections and share them with friends.',
        'shared_desc'       => 'Collections that your friends have shared with you.',
        'friends_desc'      => 'Send friend requests, manage your connections, and invite new users.',
        'admin_desc'        => 'Full system management - users, stations, measurements, collections.',
        'pending_friend_request_msg' => 'You have %d pending friend request(s).',
        'shared_with_you'   => '%s shared a collection with you.',
        'sent_you_request'  => '%s sent you a friend request.',
        'accepted_request'  => '%s accepted your friend request.',
        'sent_you_message'  => '%s sent you a message.',
        'all_users'         => 'All Users',
        'all_stations'      => 'All Stations',
        'all_collections'   => 'All Collections',
        'create_user'       => 'Create User',
        'add_station'       => 'Create Station',
        'owner'             => 'Owner',
        'unassigned'        => 'Unassigned',
        'optional'          => 'optional',
        'rename'            => 'Rename',
        'actions'           => 'Actions',
        'unread'            => 'unread',
    ],

    'uk' => [
        'dashboard'         => 'Панель керування',
        'stations'          => 'Станції',
        'measurements'      => 'Вимірювання',
        'collections'       => 'Колекції',
        'my_collections'    => 'Мої колекції',
        'shared_with_me'    => 'Поділились зі мною',
        'friends'           => 'Друзі',
        'chat'              => 'Чат',
        'notifications'     => 'Сповіщення',
        'admin'             => 'Адмін-панель',
        'account'           => 'Обліковий запис',
        'logout'            => 'Вийти',
        'login'             => 'Увійти',
        'register'          => 'Реєстрація',
        'welcome_back'      => 'З поверненням',
        'overview'          => 'Огляд моніторингу клімату в приміщенні',
        'no_stations'       => 'У вас ще немає станцій.',
        'no_data'           => 'Даних поки немає.',
        'save'              => 'Зберегти',
        'save_changes'      => 'Зберегти зміни',
        'delete'            => 'Видалити',
        'cancel'            => 'Скасувати',
        'send'              => 'Надіслати',
        'edit'              => 'Редагувати',
        'view'              => 'Переглянути',
        'open'              => 'Відкрити',
        'temperature'       => 'Температура',
        'humidity'          => 'Вологість',
        'pressure'          => 'Тиск',
        'light'             => 'Освітленість',
        'gas'               => 'Якість повітря',
        'last_updated'      => 'Останнє оновлення',
        'last_24h'          => 'Останні 24 год',
        'from'              => 'Від',
        'to'                => 'До',
        'show'              => 'Показати',
        'name'              => 'Назва',
        'description'       => 'Опис',
        'username'          => 'Ім\'я користувача',
        'email'             => 'Ел. пошта',
        'password'          => 'Пароль',
        'first_name'        => 'Ім\'я',
        'last_name'         => 'Прізвище',
        'role'              => 'Роль',
        'theme'             => 'Тема',
        'language'          => 'Мова',
        'dark'              => 'Темна',
        'theme_light'       => 'Світла',
        'friend_requests'   => 'Запити дружби',
        'add_friend'        => 'Додати друга',
        'remove_friend'     => 'Видалити друга',
        'accept'            => 'Прийняти',
        'decline'           => 'Відхилити',
        'sent_requests'     => 'Надіслані запити',
        'incoming_requests' => 'Вхідні запити',
        'invite_link'       => 'Запрошувальне посилання',
        'generate_invite'   => 'Створити запрошення',
        'invite_desc'       => 'Створіть одноразове посилання, щоб запросити нову людину в PIF (дійсне 7 днів).',
        'used_by'           => 'Використано',
        'pending'           => 'Очікує',
        'share_collection'  => 'Поділитись колекцією',
        'create_collection' => 'Створити колекцію',
        'register_station'  => 'Зареєструвати станцію',
        'serial_number'     => 'Серійний номер',
        'station'           => 'Станція',
        'station_name'      => 'Назва станції',
        'edit_station'      => 'Редагувати станцію',
        'edit_collection'   => 'Редагувати колекцію',
        'station_measurements' => 'Вимірювання станції',
        'chart_view'        => 'Графік',
        'table_view'        => 'Таблиця',
        'select_metric'     => 'Оберіть метрику',
        'records'           => 'записів',
        'all'               => 'Всі',
        'none'              => 'Жодного',
        'no_collections'    => 'У вас ще немає колекцій.',
        'no_shared'         => 'Жодних спільних колекцій ще немає.',
        'no_friends'        => 'Друзів поки немає.',
        'shared_with'       => 'Поділились з',
        'measurements_unit' => 'вимірювань',
        'creator'           => 'Автор',
        'no_measurements'   => 'Вимірювань не знайдено за вибраний період.',
        'send_request'      => 'Надіслати запит',
        'view_data'         => 'Переглянути дані',
        'mark_all_read'     => 'Позначити все як прочитане',
        'no_notifications'  => 'Сповіщень немає.',
        'type_message'      => 'Введіть повідомлення...',
        'select_friend'     => 'Оберіть друга для чату',
        'no_messages_yet'   => 'Повідомлень ще немає. Привітайтесь!',
        'add_friends_first' => 'Спочатку додайте друзів, щоб почати чат.',
        'go_to_friends'     => 'До друзів',
        'new_password'      => 'Новий пароль',
        'current_password'  => 'Поточний пароль',
        'confirm_password'  => 'Підтвердьте пароль',
        'leave_pwd_blank'   => 'Залиште порожнім, щоб зберегти поточний пароль',
        'cannot_be_changed' => '(не можна змінити)',
        'account_desc'      => 'Керуйте своїм профілем, паролем та налаштуваннями.',
        'stations_desc'     => 'Реєструйте та керуйте вашими метеостанціями. Введіть серійний номер, щоб прив\'язати пристрій.',
        'measurements_desc' => 'Фільтруйте та візуалізуйте дані сенсорів. Перемикайтесь між таблицею та графіком.',
        'collections_desc'  => 'Групуйте вимірювання у колекції та діліться ними з друзями.',
        'shared_desc'       => 'Колекції, якими поділились з вами ваші друзі.',
        'friends_desc'      => 'Надсилайте запити дружби, керуйте контактами та запрошуйте нових користувачів.',
        'admin_desc'        => 'Повне управління системою — користувачі, станції, вимірювання, колекції.',
        'pending_friend_request_msg' => 'У вас %d запит(ів) дружби.',
        'shared_with_you'   => '%s поділився колекцією з вами.',
        'sent_you_request'  => '%s надіслав запит дружби.',
        'accepted_request'  => '%s прийняв ваш запит дружби.',
        'sent_you_message'  => '%s надіслав вам повідомлення.',
        'all_users'         => 'Всі користувачі',
        'all_stations'      => 'Всі станції',
        'all_collections'   => 'Всі колекції',
        'create_user'       => 'Створити користувача',
        'add_station'       => 'Створити станцію',
        'owner'             => 'Власник',
        'unassigned'        => 'Не призначено',
        'optional'          => 'необов\'язково',
        'rename'            => 'Перейменувати',
        'actions'           => 'Дії',
        'unread'            => 'непрочитано',
    ],

    'lb' => [
        'dashboard'         => 'Iwwersiicht',
        'stations'          => 'Statiounen',
        'measurements'      => 'Miesswäerter',
        'collections'       => 'Sammlungen',
        'my_collections'    => 'Meng Sammlungen',
        'shared_with_me'    => 'Mat mir gedeelt',
        'friends'           => 'Frënn',
        'chat'              => 'Chat',
        'notifications'     => 'Notifikatiounen',
        'admin'             => 'Admin-Panel',
        'account'           => 'Kont',
        'logout'            => 'Ofmellen',
        'login'             => 'Aloggen',
        'register'          => 'Registréieren',
        'welcome_back'      => 'Wëllkomm zréck',
        'overview'          => 'Iwwersiicht vum Klima-Monitoring',
        'no_stations'       => 'Dir hutt nach keng Statioun.',
        'no_data'           => 'Nach keng Daten.',
        'save'              => 'Späicheren',
        'save_changes'      => 'Ännerungen späicheren',
        'delete'            => 'Läschen',
        'cancel'            => 'Ofbriechen',
        'send'              => 'Schécken',
        'edit'              => 'Änneren',
        'view'              => 'Weisen',
        'open'              => 'Opmaachen',
        'temperature'       => 'Temperatur',
        'humidity'          => 'Loftfiichtegkeet',
        'pressure'          => 'Loftdrock',
        'light'             => 'Luucht',
        'gas'               => 'Loftqualitéit',
        'last_updated'      => 'Zoulescht aktualiséiert',
        'last_24h'          => 'Lescht 24 St',
        'from'              => 'Vun',
        'to'                => 'Bis',
        'show'              => 'Weisen',
        'name'              => 'Numm',
        'description'       => 'Beschreiwung',
        'username'          => 'Benotzernumm',
        'email'             => 'E-Mail',
        'password'          => 'Passwuert',
        'first_name'        => 'Virnumm',
        'last_name'         => 'Familljenumm',
        'role'              => 'Roll',
        'theme'             => 'Theme',
        'language'          => 'Sprooch',
        'dark'              => 'Däischter',
        'theme_light'       => 'Hell',
        'friend_requests'   => 'Frëndschaftsufroen',
        'add_friend'        => 'Frënd bäisetzen',
        'remove_friend'     => 'Frënd ewechhuelen',
        'accept'            => 'Unhuelen',
        'decline'           => 'Ofleenen',
        'sent_requests'     => 'Geschéckt Ufroen',
        'incoming_requests' => 'Erakomm Ufroen',
        'invite_link'       => 'Invitatiounslink',
        'generate_invite'   => 'Invitatiounslink generéieren',
        'invite_desc'       => 'Generéiert e Link fir een Neies op PIF anzelueden (gëlteg 7 Deeg).',
        'used_by'           => 'Benotzt vun',
        'pending'           => 'An der Schwief',
        'share_collection'  => 'Sammlung deelen',
        'create_collection' => 'Sammlung uleeën',
        'register_station'  => 'Statioun registréieren',
        'serial_number'     => 'Seriennummer',
        'station'           => 'Statioun',
        'station_name'      => 'Numm vun der Statioun',
        'edit_station'      => 'Statioun änneren',
        'edit_collection'   => 'Sammlung änneren',
        'station_measurements' => 'Miesswäerter vun der Statioun',
        'chart_view'        => 'Diagramm',
        'table_view'        => 'Tabell',
        'select_metric'     => 'Metrik wielen',
        'records'           => 'Opzeechnungen',
        'all'               => 'All',
        'none'              => 'Keng',
        'no_collections'    => 'Dir hutt nach keng Sammlungen.',
        'no_shared'         => 'Nach keng Sammlungen mat iech gedeelt.',
        'no_friends'        => 'Nach keng Frënn.',
        'shared_with'       => 'Gedeelt mat',
        'measurements_unit' => 'Miesswäerter',
        'creator'           => 'Ersteller',
        'no_measurements'   => 'Keng Miesswäerter fir déi gewielten Period.',
        'send_request'      => 'Ufro schécken',
        'view_data'         => 'Daten weisen',
        'mark_all_read'     => 'All als gelies markéieren',
        'no_notifications'  => 'Keng Notifikatiounen.',
        'type_message'      => 'Noriicht aginn...',
        'select_friend'     => 'Frënd fir ze chaten auswielen',
        'no_messages_yet'   => 'Nach keng Noriichten. Sot Moien!',
        'add_friends_first' => 'Setzt eréischt Frënn bäi fir ze chaten.',
        'go_to_friends'     => 'Bei d\'Frënn',
        'new_password'      => 'Neit Passwuert',
        'current_password'  => 'Aktuellt Passwuert',
        'confirm_password'  => 'Passwuert bestätegen',
        'leave_pwd_blank'   => 'Eidel loossen fir dat aktuellt ze behalen',
        'cannot_be_changed' => '(kann net geännert ginn)',
        'account_desc'      => 'Verwalt äre Profil, Passwuert a Virléiften.',
        'stations_desc'     => 'Registréiert a verwalt är Wiederstatiounen. Gitt d\'Seriennummer an.',
        'measurements_desc' => 'Filtert a visualiséiert Sensordaten. Wiesselt tëscht Tabell- an Diagrammusiicht.',
        'collections_desc'  => 'Gruppéiert är Miesswäerter a Sammlungen a deelt se mat Frënn.',
        'shared_desc'       => 'Sammlungen déi är Frënn mat iech gedeelt hunn.',
        'friends_desc'      => 'Schéckt Frëndschaftsufroen, verwalt är Verbindungen an luet Neier an.',
        'admin_desc'        => 'Komplett Systemverwaltung — Benotzer, Statiounen, Miesswäerter, Sammlungen.',
        'pending_friend_request_msg' => 'Dir hutt %d Frëndschaftsufro(en).',
        'shared_with_you'   => '%s huet eng Sammlung mat iech gedeelt.',
        'sent_you_request'  => '%s huet iech eng Frëndschaftsufro geschéckt.',
        'accepted_request'  => '%s huet är Frëndschaftsufro ugeholl.',
        'sent_you_message'  => '%s huet iech eng Noriicht geschéckt.',
        'all_users'         => 'All Benotzer',
        'all_stations'      => 'All Statiounen',
        'all_collections'   => 'All Sammlungen',
        'create_user'       => 'Benotzer uleeën',
        'add_station'       => 'Statioun uleeën',
        'owner'             => 'Besëtzer',
        'unassigned'        => 'Net zougewisen',
        'optional'          => 'optional',
        'rename'            => 'Ëmbenennen',
        'actions'           => 'Aktiounen',
        'unread'            => 'ongelies',
    ],
];

// Get current interface language
function getLang()
{
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'uk', 'lb'])) {
        return $_SESSION['language'];
    }
    if (isset($_COOKIE['pif_lang']) && in_array($_COOKIE['pif_lang'], ['en', 'uk', 'lb'])) {
        return $_COOKIE['pif_lang'];
    }
    return 'en';
}

// Get current theme
function getTheme()
{
    if (isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['dark', 'light'])) {
        return $_SESSION['theme'];
    }
    if (isset($_COOKIE['pif_theme']) && in_array($_COOKIE['pif_theme'], ['dark', 'light'])) {
        return $_COOKIE['pif_theme'];
    }
    return 'dark';
}

// Translate a key
function t($key)
{
    global $TRANSLATIONS;
    $lang = getLang();
    if (isset($TRANSLATIONS[$lang][$key])) return $TRANSLATIONS[$lang][$key];
    if (isset($TRANSLATIONS['en'][$key]))  return $TRANSLATIONS['en'][$key];
    return $key;
}

// Function to render the navigation bar
function NavigationBar($activePage)
{
    if (!isset($_SESSION['username'])) {
        return;
    }
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    $unreadNotifs = getUnreadNotificationCount($_SESSION['username']);
    $unreadMsgs   = getUnreadMessageCount($_SESSION['username']);
    $lang  = getLang();
    $theme = getTheme();
?>
    <nav>
        <a href="Dashboard.php" class="nav-logo">
            <span class="lp">P</span><span class="li">I</span><span class="lf">F</span>
            <span class="nav-logo-sub">Portable Indoor Feedback</span>
            <span class="nav-live-dot"></span>
        </a>

        <div class="nav-links">
            <a href="Dashboard.php" class="nav-link <?php if ($activePage == 'dashboard') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
                <?php echo t('dashboard'); ?>
            </a>
            <a href="Stations.php" class="nav-link <?php if ($activePage == 'stations') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                    <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                    <circle cx="12" cy="20" r="1" fill="currentColor" />
                </svg>
                <?php echo t('stations'); ?>
            </a>
            <a href="Measurements.php" class="nav-link <?php if ($activePage == 'measurements') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                </svg>
                <?php echo t('measurements'); ?>
            </a>
            <a href="MyCollections.php" class="nav-link <?php if ($activePage == 'collections') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                </svg>
                <?php echo t('collections'); ?>
            </a>
            <a href="Shared.php" class="nav-link <?php if ($activePage == 'shared') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="5" r="3" />
                    <circle cx="6" cy="12" r="3" />
                    <circle cx="18" cy="19" r="3" />
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                </svg>
                <?php echo t('shared_with_me'); ?>
            </a>
            <a href="Friends.php" class="nav-link <?php if ($activePage == 'friends') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <?php echo t('friends'); ?>
            </a>
            <a href="Chat.php" class="nav-link <?php if ($activePage == 'chat') echo 'active'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                <?php echo t('chat'); ?>
                <?php if ($unreadMsgs > 0): ?>
                    <span class="nav-pill-badge"><?php echo (int)$unreadMsgs; ?></span>
                <?php endif; ?>
            </a>
            <?php if ($isAdmin): ?>
                <a href="AdminPanel.php" class="nav-link <?php if ($activePage == 'admin') echo 'active'; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M4.93 19.07l1.41-1.41M19.07 19.07l-1.41-1.41M20 12h2M2 12h2M12 20v2M12 2v2" />
                    </svg>
                    <?php echo t('admin'); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="nav-spacer"></div>

        <div class="nav-right">
            <!-- Notifications -->
            <button class="nav-icon-btn" id="notifToggle" type="button" title="<?php echo t('notifications'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
                <?php if ($unreadNotifs > 0): ?>
                    <span class="notif-badge"><?php echo (int)$unreadNotifs; ?></span>
                <?php endif; ?>
            </button>

            <!-- Theme toggle -->
            <button class="nav-icon-btn" id="themeToggle" type="button" title="<?php echo t('theme'); ?>">
                <?php if ($theme === 'dark'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5" />
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                    </svg>
                <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                <?php endif; ?>
            </button>

            <!-- Language selector -->
            <select id="langSelect" title="<?php echo t('language'); ?>" style="width:auto;padding:.26rem .4rem;font-size:.75rem;border-radius:7px;">
                <option value="en" <?php if ($lang === 'en') echo 'selected'; ?>>EN</option>
                <option value="uk" <?php if ($lang === 'uk') echo 'selected'; ?>>UK</option>
                <option value="lb" <?php if ($lang === 'lb') echo 'selected'; ?>>LB</option>
            </select>

            <!-- Account -->
            <a href="Account.php" class="nav-link <?php if ($activePage == 'account') echo 'active'; ?>" style="gap:.4rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;opacity:.65;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <?php echo htmlspecialchars($_SESSION['firstName']); ?>
                <?php if ($isAdmin): ?><span class="badge badge-admin">Admin</span><?php endif; ?>
            </a>
            <a class="btn-logout" href="Logout.php"><?php echo t('logout'); ?></a>
            <button class="hamburger" id="hamburgerBtn" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;">
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </button>
        </div>
    </nav>

    <div class="nav-mobile-menu" id="mobileMenu">
        <a href="Dashboard.php"><?php echo t('dashboard'); ?></a>
        <a href="Stations.php"><?php echo t('stations'); ?></a>
        <a href="Measurements.php"><?php echo t('measurements'); ?></a>
        <a href="MyCollections.php"><?php echo t('collections'); ?></a>
        <a href="Shared.php"><?php echo t('shared_with_me'); ?></a>
        <a href="Friends.php"><?php echo t('friends'); ?></a>
        <a href="Chat.php"><?php echo t('chat'); ?></a>
        <?php if ($isAdmin): ?>
            <a href="AdminPanel.php"><?php echo t('admin'); ?></a>
        <?php endif; ?>
        <a href="Account.php"><?php echo t('account'); ?></a>
        <a href="Logout.php"><?php echo t('logout'); ?></a>
    </div>

    <!-- Notifications dropdown panel -->
    <div class="notif-dropdown" id="notifDropdown">
        <div class="notif-header">
            <span><?php echo t('notifications'); ?></span>
            <button type="button" class="btn-xs" id="markAllReadBtn"><?php echo t('mark_all_read'); ?></button>
        </div>
        <div id="notifList">
            <div class="empty" style="padding:1.5rem;"><?php echo t('no_notifications'); ?></div>
        </div>
    </div>

    <script>
        (function() {
            // Mobile hamburger
            var hamb = document.getElementById('hamburgerBtn');
            var menu = document.getElementById('mobileMenu');
            if (hamb && menu) {
                hamb.onclick = function(e) {
                    e.stopPropagation();
                    menu.classList.toggle('open');
                };
                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && e.target !== hamb) menu.classList.remove('open');
                });
            }

            // Notifications dropdown
            var notifBtn = document.getElementById('notifToggle');
            var notifDD = document.getElementById('notifDropdown');
            var notifList = document.getElementById('notifList');
            var markAllBtn = document.getElementById('markAllReadBtn');

            function loadNotifications() {
                fetch('Api.php?action=notifications').then(function(r) {
                    return r.json();
                }).then(function(data) {
                    if (!data.items || data.items.length === 0) {
                        notifList.innerHTML = '<div class="empty" style="padding:1.5rem;"><?php echo t('no_notifications'); ?></div>';
                        return;
                    }
                    var icons = {
                        friend_request: '\u{1F465}',
                        share: '\u{1F4C1}',
                        message: '\u{1F4AC}'
                    };
                    var html = '';
                    for (var i = 0; i < data.items.length; i++) {
                        var n = data.items[i];
                        var icon = icons[n.type] || '\u{1F514}';
                        var msg = String(n.message).replace(/[&<>"']/g, function(c) {
                            return ({
                                '&': '&amp;',
                                '<': '&lt;',
                                '>': '&gt;',
                                '"': '&quot;',
                                "'": '&#39;'
                            })[c];
                        });
                        html += '<div class="notif-item' + (n.is_read == 0 ? ' unread' : '') + '">' +
                            '<span style="font-size:1.1rem; flex-shrink:0;">' + icon + '</span>' +
                            '<div style="flex:1;"><div>' + msg + '</div>' +
                            (n.link ? '<a href="' + n.link + '">View &rarr;</a>' : '') +
                            '<div class="notif-time">' + n.created_at + '</div></div></div>';
                    }
                    notifList.innerHTML = html;
                });
            }

            if (notifBtn) {
                notifBtn.onclick = function(e) {
                    e.stopPropagation();
                    if (notifDD.style.display === 'block') {
                        notifDD.style.display = 'none';
                        return;
                    }
                    notifDD.style.display = 'block';
                    loadNotifications();
                };
                document.addEventListener('click', function(e) {
                    if (!notifDD.contains(e.target) && e.target !== notifBtn) notifDD.style.display = 'none';
                });
            }
            if (markAllBtn) {
                markAllBtn.onclick = function(e) {
                    e.stopPropagation();
                    var fd = new FormData();
                    fd.append('action', 'mark_notifs_read');
                    fetch('Api.php', {
                        method: 'POST',
                        body: fd
                    }).then(function() {
                        var b = document.querySelector('.notif-badge');
                        if (b) b.remove();
                        document.querySelectorAll('.notif-item.unread').forEach(function(el) {
                            el.classList.remove('unread');
                        });
                    });
                };
            }

            // Theme toggle
            var themeBtn = document.getElementById('themeToggle');
            if (themeBtn) {
                themeBtn.onclick = function() {
                    var current = document.documentElement.getAttribute('data-theme') || 'dark';
                    var next = current === 'dark' ? 'light' : 'dark';
                    var fd = new FormData();
                    fd.append('action', 'set_theme');
                    fd.append('theme', next);
                    fetch('Api.php', {
                        method: 'POST',
                        body: fd
                    }).then(function() {
                        location.reload();
                    });
                };
            }

            // Language selector
            var langSel = document.getElementById('langSelect');
            if (langSel) {
                langSel.onchange = function() {
                    var fd = new FormData();
                    fd.append('action', 'set_lang');
                    fd.append('lang', this.value);
                    fetch('Api.php', {
                        method: 'POST',
                        body: fd
                    }).then(function() {
                        location.reload();
                    });
                };
            }
        })();
    </script>
<?php
}

// Function to require login
function requireLogin()
{
    if (!isset($_SESSION['username'])) {
        header("Location: Login.php");
        exit();
    }
}

// Function to require admin role
function requireAdmin()
{
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        http_response_code(403);
        echo "Forbidden: admin access required.";
        exit();
    }
}

// Function to check if username exists
function userExists($username)
{
    global $conn;
    $stmt = $conn->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to check if email exists
function emailExists($email)
{
    global $conn;
    $stmt = $conn->prepare("SELECT pk_username FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to get user by username (selects only the columns we always need)
function getUserByUsername($username)
{
    global $conn;
    $stmt = $conn->prepare("SELECT pk_username, firstName, lastName, email, password, role, theme, language FROM user WHERE pk_username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->num_rows == 1 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $user;
}

// Verify password for a given username
function verifyPassword($username, $password)
{
    $user = getUserByUsername($username);
    if (!$user) return false;
    return password_verify($password, $user['password']);
}

// Update user in database
function updateUser($username, $firstName, $lastName, $email, $passwordHash, $theme = 'dark', $language = 'en')
{
    global $conn;
    $stmt = $conn->prepare("
        UPDATE user
        SET firstName = ?, lastName = ?, email = ?, password = ?, theme = ?, language = ?
        WHERE pk_username = ?
    ");
    $stmt->bind_param("sssssss", $firstName, $lastName, $email, $passwordHash, $theme, $language, $username);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Update session data after account edit
function updateSessionUser($firstName, $lastName, $email, $theme = null, $language = null)
{
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName']  = $lastName;
    $_SESSION['email']     = $email;
    if ($theme !== null)    $_SESSION['theme']    = $theme;
    if ($language !== null) $_SESSION['language'] = $language;
}

// ===== Friend helpers =====

// Check if two users are friends
function areFriends($u1, $u2)
{
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?");
    $stmt->bind_param("ss", $u1, $u2);
    $stmt->execute();
    $res = $stmt->get_result();
    $are = $res->num_rows > 0;
    $stmt->close();
    return $are;
}

// Get friends usernames of a user
function getFriends($username)
{
    global $conn;
    $friends = [];
    $stmt = $conn->prepare("SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = ? ORDER BY pkfk_user_friend");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row['pkfk_user_friend'];
    }
    $stmt->close();
    return $friends;
}

// Get friends with their first/last names for displaying
function getFriendsWithInfo($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT u.pk_username, u.firstName, u.lastName, u.email FROM isfriend f JOIN user u ON f.pkfk_user_friend = u.pk_username WHERE f.pkfk_user_user = ? ORDER BY u.firstName");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

// Send a friend request from one user to another
function sendFriendRequest($fromUsername, $toUsername)
{
    global $conn;
    if ($fromUsername === $toUsername) return [false, 'Cannot send request to yourself'];
    if (!userExists($toUsername)) return [false, 'User not found'];
    if (areFriends($fromUsername, $toUsername)) return [false, 'Already friends'];

    // Did they already send us a pending request?
    $stmt = $conn->prepare("SELECT 1 FROM friendrequest WHERE fk_sender = ? AND fk_receiver = ? AND status = 'pending'");
    $stmt->bind_param("ss", $toUsername, $fromUsername);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $stmt->close();
        return [false, 'This user already sent you a request'];
    }
    $stmt->close();

    // Did we already send them something?
    $stmt = $conn->prepare("SELECT status FROM friendrequest WHERE fk_sender = ? AND fk_receiver = ?");
    $stmt->bind_param("ss", $fromUsername, $toUsername);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $stmt->close();
        if ($row['status'] === 'pending') return [false, 'Request already pending'];
        // If declined before, allow re-sending by updating to pending
        $upd = $conn->prepare("UPDATE friendrequest SET status = 'pending', created_at = NOW() WHERE fk_sender = ? AND fk_receiver = ?");
        $upd->bind_param("ss", $fromUsername, $toUsername);
        $ok = $upd->execute();
        $upd->close();
        if ($ok) {
            $sender = getUserByUsername($fromUsername);
            $msg = sprintf(t('sent_you_request'), ($sender ? $sender['firstName'] . ' ' . $sender['lastName'] : $fromUsername));
            createNotification($toUsername, 'friend_request', $msg, 'Friends.php');
        }
        return [$ok, $ok ? 'Request sent' : 'Database error'];
    }
    $stmt->close();

    $ins = $conn->prepare("INSERT INTO friendrequest (fk_sender, fk_receiver, status) VALUES (?, ?, 'pending')");
    $ins->bind_param("ss", $fromUsername, $toUsername);
    $ok = $ins->execute();
    $ins->close();
    if ($ok) {
        $sender = getUserByUsername($fromUsername);
        $msg = sprintf(t('sent_you_request'), ($sender ? $sender['firstName'] . ' ' . $sender['lastName'] : $fromUsername));
        createNotification($toUsername, 'friend_request', $msg, 'Friends.php');
    }
    return [$ok, $ok ? 'Request sent' : 'Database error'];
}

// Cancel a sent friend request
function cancelFriendRequest($fromUsername, $toUsername)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM friendrequest WHERE fk_sender = ? AND fk_receiver = ? AND status = 'pending'");
    $stmt->bind_param("ss", $fromUsername, $toUsername);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

// Get usernames of pending requests we sent
function getSentRequests($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT fk_receiver FROM friendrequest WHERE fk_sender = ? AND status = 'pending' ORDER BY created_at DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r['fk_receiver'];
    $stmt->close();
    return $out;
}

// Get usernames of pending requests sent to us
function getIncomingRequests($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT fk_sender FROM friendrequest WHERE fk_receiver = ? AND status = 'pending' ORDER BY created_at DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r['fk_sender'];
    $stmt->close();
    return $out;
}

// Accept an incoming friend request
function acceptFriendRequest($currentUsername, $fromUsername)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE friendrequest SET status = 'accepted' WHERE fk_sender = ? AND fk_receiver = ? AND status = 'pending'");
    $stmt->bind_param("ss", $fromUsername, $currentUsername);
    if (!$stmt->execute() || $stmt->affected_rows === 0) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    // Insert friendship in both directions because isfriend is directional
    $ins = $conn->prepare("INSERT IGNORE INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?)");
    $ins->bind_param("ss", $currentUsername, $fromUsername);
    $ins->execute();
    $ins->bind_param("ss", $fromUsername, $currentUsername);
    $ins->execute();
    $ins->close();

    // Notify the original requester
    $accepter = getUserByUsername($currentUsername);
    $msg = sprintf(t('accepted_request'), ($accepter ? $accepter['firstName'] . ' ' . $accepter['lastName'] : $currentUsername));
    createNotification($fromUsername, 'friend_request', $msg, 'Friends.php');
    return true;
}

// Decline an incoming friend request
function declineFriendRequest($currentUsername, $fromUsername)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE friendrequest SET status = 'declined' WHERE fk_sender = ? AND fk_receiver = ? AND status = 'pending'");
    $stmt->bind_param("ss", $fromUsername, $currentUsername);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

// Remove a friend (and revoke any shared collection access between them)
function removeFriend($username, $friendUsername)
{
    global $conn;
    $stmt = $conn->prepare("DELETE ha FROM hasaccess ha JOIN collection c ON ha.pkfk_collection = c.pk_collection WHERE ha.pkfk_user = ? AND c.fk_user_creates = ?");
    $stmt->bind_param("ss", $friendUsername, $username);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE ha FROM hasaccess ha JOIN collection c ON ha.pkfk_collection = c.pk_collection WHERE ha.pkfk_user = ? AND c.fk_user_creates = ?");
    $stmt->bind_param("ss", $username, $friendUsername);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM isfriend WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?) OR (pkfk_user_user = ? AND pkfk_user_friend = ?)");
    $stmt->bind_param("ssss", $username, $friendUsername, $friendUsername, $username);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// ===== Station helpers =====

function fetchStationsForUser($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT pk_serialNumber, name, description FROM station WHERE fk_user_owns = ? ORDER BY name, pk_serialNumber");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

function getStationBySerial($serial)
{
    global $conn;
    $stmt = $conn->prepare("SELECT pk_serialNumber, name, description, fk_user_owns FROM station WHERE pk_serialNumber = ?");
    $stmt->bind_param("s", $serial);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->num_rows ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function claimStation($serial, $username)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ? AND fk_user_owns IS NULL");
    $stmt->bind_param("ss", $username, $serial);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

function updateStation($serial, $username, $name, $description)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE station SET name = ?, description = ? WHERE pk_serialNumber = ? AND fk_user_owns = ?");
    $stmt->bind_param("ssss", $name, $description, $serial, $username);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function releaseStation($serial, $username)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE station SET fk_user_owns = NULL WHERE pk_serialNumber = ? AND fk_user_owns = ?");
    $stmt->bind_param("ss", $serial, $username);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

// Fetch stations for a user with their latest measurement (one row per station)
function fetchStationsWithLatestMeasurement($username)
{
    global $conn;
    $out = [];
    $sql = "SELECT s.pk_serialNumber, s.name, s.description,
                   m.timestamp, m.temperature, m.humidity, m.pressure, m.light, m.gas
            FROM station s
            LEFT JOIN measurement m ON m.pk_measurement = (
                SELECT m2.pk_measurement FROM measurement m2 WHERE m2.fk_station_records = s.pk_serialNumber ORDER BY m2.timestamp DESC LIMIT 1
            )
            WHERE s.fk_user_owns = ?
            ORDER BY s.name, s.pk_serialNumber";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    $stmt->close();
    return $out;
}

// Fetch the last 24h of measurements for one station
function fetchLast24hForStation($serial)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT timestamp, temperature, humidity, pressure, light, gas FROM measurement WHERE fk_station_records = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY timestamp ASC LIMIT 200");
    $stmt->bind_param("s", $serial);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

// ===== Measurement helpers =====

function fetchMeasurementIDs($serial, $startSql = '', $endSql = '')
{
    global $conn;
    $ids = [];
    if ($serial === '') return $ids;
    if ($startSql !== '' && $endSql !== '') {
        $q = $conn->prepare("SELECT pk_measurement FROM measurement WHERE fk_station_records = ? AND timestamp BETWEEN ? AND ? ORDER BY timestamp DESC");
        $q->bind_param("sss", $serial, $startSql, $endSql);
    } elseif ($startSql !== '') {
        $q = $conn->prepare("SELECT pk_measurement FROM measurement WHERE fk_station_records = ? AND timestamp >= ? ORDER BY timestamp DESC");
        $q->bind_param("ss", $serial, $startSql);
    } elseif ($endSql !== '') {
        $q = $conn->prepare("SELECT pk_measurement FROM measurement WHERE fk_station_records = ? AND timestamp <= ? ORDER BY timestamp DESC");
        $q->bind_param("ss", $serial, $endSql);
    } else {
        $q = $conn->prepare("SELECT pk_measurement FROM measurement WHERE fk_station_records = ? ORDER BY timestamp DESC LIMIT 1000");
        $q->bind_param("s", $serial);
    }
    if ($q->execute()) {
        $r = $q->get_result();
        while ($m = $r->fetch_assoc()) {
            $ids[] = (int)$m['pk_measurement'];
        }
    }
    $q->close();
    return $ids;
}

function fetchMeasurementsForStation($serial, $startSql = '', $endSql = '')
{
    global $conn;
    $out = [];
    if ($serial === '') return $out;
    if ($startSql !== '' && $endSql !== '') {
        $q = $conn->prepare("SELECT pk_measurement, timestamp, temperature, humidity, pressure, light, gas FROM measurement WHERE fk_station_records = ? AND timestamp BETWEEN ? AND ? ORDER BY timestamp DESC");
        $q->bind_param("sss", $serial, $startSql, $endSql);
    } elseif ($startSql !== '') {
        $q = $conn->prepare("SELECT pk_measurement, timestamp, temperature, humidity, pressure, light, gas FROM measurement WHERE fk_station_records = ? AND timestamp >= ? ORDER BY timestamp DESC");
        $q->bind_param("ss", $serial, $startSql);
    } elseif ($endSql !== '') {
        $q = $conn->prepare("SELECT pk_measurement, timestamp, temperature, humidity, pressure, light, gas FROM measurement WHERE fk_station_records = ? AND timestamp <= ? ORDER BY timestamp DESC");
        $q->bind_param("ss", $serial, $endSql);
    } else {
        $q = $conn->prepare("SELECT pk_measurement, timestamp, temperature, humidity, pressure, light, gas FROM measurement WHERE fk_station_records = ? ORDER BY timestamp DESC LIMIT 1000");
        $q->bind_param("s", $serial);
    }
    if ($q->execute()) {
        $r = $q->get_result();
        while ($m = $r->fetch_assoc()) $out[] = $m;
    }
    $q->close();
    return $out;
}

function deleteMeasurement($measurementID, $currentUsername, $adminBypass = false)
{
    global $conn;
    if ($adminBypass) {
        $stmt = $conn->prepare("DELETE FROM measurement WHERE pk_measurement = ?");
        $stmt->bind_param("i", $measurementID);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    $stmt = $conn->prepare("DELETE m FROM measurement m JOIN station s ON m.fk_station_records = s.pk_serialNumber WHERE m.pk_measurement = ? AND s.fk_user_owns = ?");
    $stmt->bind_param("is", $measurementID, $currentUsername);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

// ===== Collection helpers =====

function createCollectionFromMeasurements($creatorUsername, $name, $description, $measurementIDs)
{
    global $conn;
    if (empty($measurementIDs) || trim($name) === '') return [false, 'Invalid input', 0];
    $name = mb_substr($name, 0, 50);
    $ins = $conn->prepare("INSERT INTO collection (name, description, fk_user_creates) VALUES (?, ?, ?)");
    $ins->bind_param("sss", $name, $description, $creatorUsername);
    if (!$ins->execute()) {
        $ins->close();
        return [false, 'Database error while creating collection.', 0];
    }
    $newCID = $conn->insert_id;
    $ins->close();
    $cm = $conn->prepare("INSERT IGNORE INTO contains (pkfk_collection, pkfk_measurement) VALUES (?, ?)");
    foreach ($measurementIDs as $mid) {
        $cm->bind_param("ii", $newCID, $mid);
        $cm->execute();
    }
    $cm->close();
    return [true, 'Collection created successfully.', $newCID];
}

function getUserCollections($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT c.pk_collection, c.name, c.description, (SELECT COUNT(*) FROM contains ct WHERE ct.pkfk_collection = c.pk_collection) AS measurement_count FROM collection c WHERE c.fk_user_creates = ? ORDER BY c.name");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

function getCollectionByID($collectionID)
{
    global $conn;
    $stmt = $conn->prepare("SELECT c.pk_collection, c.name, c.description, c.fk_user_creates, u.firstName, u.lastName FROM collection c JOIN user u ON c.fk_user_creates = u.pk_username WHERE c.pk_collection = ?");
    $stmt->bind_param("i", $collectionID);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->num_rows ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function canUserAccessCollection($username, $collectionID)
{
    global $conn;
    if ($username === '' || $collectionID <= 0) return false;
    $stmt = $conn->prepare("SELECT fk_user_creates FROM collection WHERE pk_collection = ?");
    $stmt->bind_param("i", $collectionID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        return false;
    }
    $row = $res->fetch_assoc();
    $stmt->close();
    if ($row['fk_user_creates'] === $username) return true;

    $q = $conn->prepare("SELECT 1 FROM hasaccess WHERE pkfk_collection = ? AND pkfk_user = ? LIMIT 1");
    $q->bind_param("is", $collectionID, $username);
    $q->execute();
    $r = $q->get_result();
    $allowed = $r->num_rows > 0;
    $q->close();
    return $allowed;
}

function getMeasurementsForCollection($collectionID)
{
    global $conn;
    $out = [];
    $q = $conn->prepare("SELECT m.pk_measurement, m.timestamp, m.temperature, m.humidity, m.pressure, m.light, m.gas FROM measurement m JOIN contains ct ON m.pk_measurement = ct.pkfk_measurement WHERE ct.pkfk_collection = ? ORDER BY m.timestamp DESC");
    $q->bind_param("i", $collectionID);
    $q->execute();
    $r = $q->get_result();
    while ($row = $r->fetch_assoc()) $out[] = $row;
    $q->close();
    return $out;
}

function getCollectionAccessUsernames($collectionID)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT pkfk_user FROM hasaccess WHERE pkfk_collection = ? ORDER BY pkfk_user");
    $stmt->bind_param("i", $collectionID);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r['pkfk_user'];
    $stmt->close();
    return $out;
}

function setCollectionAccessByUsernames($collectionID, $ownerUsername, $usernames)
{
    global $conn;
    $stmt = $conn->prepare("SELECT name, fk_user_creates FROM collection WHERE pk_collection = ?");
    $stmt->bind_param("i", $collectionID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        return [false, 'Collection not found'];
    }
    $row = $res->fetch_assoc();
    $stmt->close();
    if ($row['fk_user_creates'] !== $ownerUsername) return [false, 'Not authorized'];

    // Normalize usernames to unique non-empty strings, excluding the owner
    $clean = [];
    foreach ($usernames as $u) {
        $u = trim($u);
        if ($u === '' || $u === $ownerUsername) continue;
        if (!in_array($u, $clean)) $clean[] = $u;
    }

    // Get existing access list to detect newly added users (for notifications)
    $existing = getCollectionAccessUsernames($collectionID);

    $del = $conn->prepare("DELETE FROM hasaccess WHERE pkfk_collection = ?");
    $del->bind_param("i", $collectionID);
    if (!$del->execute()) {
        $del->close();
        return [false, 'Database error while removing old access'];
    }
    $del->close();

    if (count($clean) === 0) return [true, 'Access cleared'];

    $owner = getUserByUsername($ownerUsername);
    $ownerLabel = $owner ? $owner['firstName'] . ' ' . $owner['lastName'] : $ownerUsername;

    $ins = $conn->prepare("INSERT IGNORE INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?)");
    foreach ($clean as $u) {
        if (!userExists($u)) continue;
        if (!areFriends($ownerUsername, $u)) continue;
        $ins->bind_param("si", $u, $collectionID);
        $ins->execute();
        // Notify the user only if this is a new share
        if (!in_array($u, $existing)) {
            $msg = sprintf(t('shared_with_you'), $ownerLabel);
            createNotification($u, 'share', $msg, 'Shared.php');
        }
    }
    $ins->close();
    return [true, 'Access updated'];
}

function getCollectionsSharedWithUser($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT c.pk_collection, c.name, c.description, u.firstName, u.lastName, c.fk_user_creates AS owner_username, (SELECT COUNT(*) FROM contains ct WHERE ct.pkfk_collection = c.pk_collection) AS measurement_count FROM collection c JOIN hasaccess ha ON c.pk_collection = ha.pkfk_collection JOIN user u ON c.fk_user_creates = u.pk_username WHERE ha.pkfk_user = ? ORDER BY c.name");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

function deleteUserCollection($username, $collectionID)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM collection WHERE pk_collection = ? AND fk_user_creates = ?");
    $stmt->bind_param("is", $collectionID, $username);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

function updateUserCollection($username, $collectionID, $name, $description)
{
    global $conn;
    if ($collectionID <= 0 || trim($name) === '') return false;
    $stmt = $conn->prepare("UPDATE collection SET name = ?, description = ? WHERE pk_collection = ? AND fk_user_creates = ?");
    $stmt->bind_param("ssis", $name, $description, $collectionID, $username);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// ===== Display helpers (dashboard color coding) =====

function tempColor($v)
{
    if ($v === null || $v === '') return '#60a5fa';
    $v = (float)$v;
    if ($v <= 0)  return '#93c5fd';
    if ($v <= 10) return '#67e8f9';
    if ($v <= 18) return '#34d399';
    if ($v <= 24) return '#60a5fa';
    if ($v <= 30) return '#fbbf24';
    return '#f87171';
}

function gasColor($v)
{
    if ($v === null || $v === '') return '#4ade80';
    $v = (float)$v;
    if ($v < 800)  return '#4ade80';
    if ($v < 1500) return '#fbbf24';
    return '#f87171';
}

function gasLabel($v)
{
    if ($v === null || $v === '') return '-';
    $v = (float)$v;
    if ($v < 800)  return 'Good';
    if ($v < 1500) return 'Fair';
    return 'Poor';
}

// ===== Notifications =====

function createNotification($forUser, $type, $message, $link = '')
{
    global $conn;
    if (!isset($conn) || !$forUser || !$message) return;
    $stmt = $conn->prepare("INSERT INTO notification (fk_user, type, message, link) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $forUser, $type, $message, $link);
        $stmt->execute();
        $stmt->close();
    }
}

function getNotifications($username, $limit = 30)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT pk_id, type, message, link, is_read, created_at FROM notification WHERE fk_user = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("si", $username, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

function getUnreadNotificationCount($username)
{
    global $conn;
    if (!isset($conn) || !$username) return 0;
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM notification WHERE fk_user = ? AND is_read = 0");
    if (!$stmt) return 0;
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return (int)$row['c'];
}

function markAllNotificationsRead($username)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE notification SET is_read = 1 WHERE fk_user = ? AND is_read = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

// ===== Chat / messaging =====

function getUnreadMessageCount($username)
{
    global $conn;
    if (!isset($conn) || !$username) return 0;
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM message WHERE fk_receiver = ? AND is_read = 0");
    if (!$stmt) return 0;
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return (int)$row['c'];
}

// Returns map [fromUsername => unreadCount]
function getUnreadMessagesByFriend($username)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT fk_sender, COUNT(*) AS c FROM message WHERE fk_receiver = ? AND is_read = 0 GROUP BY fk_sender");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[$r['fk_sender']] = (int)$r['c'];
    $stmt->close();
    return $out;
}

// Get last 100 messages between two users; also marks incoming messages as read
function getChatMessages($me, $friend)
{
    global $conn;
    $out = [];
    if (!areFriends($me, $friend)) return $out;
    // Mark messages from the friend as read
    $upd = $conn->prepare("UPDATE message SET is_read = 1 WHERE fk_receiver = ? AND fk_sender = ? AND is_read = 0");
    $upd->bind_param("ss", $me, $friend);
    $upd->execute();
    $upd->close();
    // Fetch
    $stmt = $conn->prepare("SELECT pk_id, fk_sender, fk_receiver, body, sent_at, is_read FROM message WHERE (fk_sender = ? AND fk_receiver = ?) OR (fk_sender = ? AND fk_receiver = ?) ORDER BY sent_at ASC LIMIT 100");
    $stmt->bind_param("ssss", $me, $friend, $friend, $me);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}

// Send a chat message (only between friends). Returns [ok, error].
function sendChatMessage($from, $to, $body)
{
    global $conn;
    $body = trim($body);
    if ($body === '') return [false, 'empty'];
    if (!areFriends($from, $to)) return [false, 'not friends'];
    $stmt = $conn->prepare("INSERT INTO message (fk_sender, fk_receiver, body) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $from, $to, $body);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $sender = getUserByUsername($from);
        $msg = sprintf(t('sent_you_message'), ($sender ? $sender['firstName'] : $from));
        createNotification($to, 'message', $msg, 'Chat.php?with=' . urlencode($from));
    }
    return [$ok, $ok ? '' : 'db error'];
}

// ===== Invite helpers =====

function createInvite($creatorUsername)
{
    global $conn;
    $token = bin2hex(random_bytes(24));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $stmt = $conn->prepare("INSERT INTO invite (pk_token, fk_creator, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $token, $creatorUsername, $expires);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok ? $token : null;
}

// Returns the creator's username if the token is valid and unused, or null otherwise.
function validateInvite($token)
{
    global $conn;
    if (!$token) return null;
    $stmt = $conn->prepare("SELECT fk_creator FROM invite WHERE pk_token = ? AND used_by IS NULL AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $creator = null;
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $creator = $row['fk_creator'];
    }
    $stmt->close();
    return $creator;
}

function markInviteUsed($token, $usedBy)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE invite SET used_by = ? WHERE pk_token = ? AND used_by IS NULL");
    $stmt->bind_param("ss", $usedBy, $token);
    $stmt->execute();
    $stmt->close();
}

function getRecentInvites($creatorUsername, $limit = 5)
{
    global $conn;
    $out = [];
    $stmt = $conn->prepare("SELECT pk_token, used_by, created_at, expires_at FROM invite WHERE fk_creator = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("si", $creatorUsername, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $out[] = $r;
    $stmt->close();
    return $out;
}
?>