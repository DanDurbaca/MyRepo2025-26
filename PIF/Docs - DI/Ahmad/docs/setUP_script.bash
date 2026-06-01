#!/bin/bash

# --- CONFIGURATION (Matches your db.php) ---
DB_NAME="portableindoorfeedback"
DB_USER="allah657"
DB_PASS=""
SQL_FILE="/var/www/html/portableindoorfeedback.sql" # Change if your file name is different

echo "------------------------------------------------"
echo "🌟 Starting Full Environment Setup..."
echo "------------------------------------------------"

# 1. Update and Install Software
echo "📦 Installing Apache, MariaDB, and PHP..."
apt update
apt install apache2 mariadb-server php libapache2-mod-php php-mysql -y

# 2. Start Services
echo "⚙️ Starting Services..."
systemctl start apache2
systemctl start mariadb
systemctl enable apache2
systemctl enable mariadb

# 3. Database Setup
echo "🗄️ Configuring Database and User..."
# Create Database
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
# Create User with no password and allow web access
mysql -e "DROP USER IF EXISTS '$DB_USER'@'localhost';"
mysql -e "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# 4. Import Data
if [ -f "$SQL_FILE" ]; then
    echo "📥 Importing SQL data from $SQL_FILE..."
    mysql $DB_NAME < "$SQL_FILE"
else
    echo "⚠️ Warning: SQL file not found at $SQL_FILE. Database is empty!"
fi

# 5. Fix Web Server Permissions
echo "🔐 Setting folder permissions..."
# Remove the default Apache index page so your index.php works
if [ -f "/var/www/html/index.html" ]; then
    rm /var/www/html/index.html
fi

# Ensure Apache (www-data) owns the files
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# 6. Enable PHP Error Reporting for the Presentation (Debugging Mode)
echo "🛠️ Enabling PHP error display for debugging..."
sed -i 's/display_errors = Off/display_errors = On/' /etc/php/*/apache2/php.ini
sed -i 's/display_startup_errors = Off/display_startup_errors = On/' /etc/php/*/apache2/php.ini

# 7. Restart Apache to apply all changes
systemctl restart apache2

echo "------------------------------------------------"
echo "✅ SETUP COMPLETE!"
echo "🌍 Visit: http://$(hostname -I | awk '{print $1}')"
echo "------------------------------------------------"