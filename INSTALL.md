# Install daloRADIUS on Debian 12
This guide will walk you through the steps to install phpIPAM on a Debian system.

## Prerequisites
Before you begin, you should have the following:

- A Debian 12 system with **root** access.
- A basic understanding of the Linux command line.

## Installation Steps

1. Update the package list:
```
apt update
```

2. Upgrade the system:
```
apt upgrade
```

3. Install Apache web server:
```
apt install apache2 apache2-utils
```

4. Install PHP and required modules:
```
apt install libapache2-mod-php php php-mysql php-cli php-gmp php-gd php-bcmath php-mbstring php-curl php-zip php-pear
```

5. Install MariaDB server:
```
apt install mariadb-server mariadb-client 
```

6. Secure the MariaDB installation:
```
mysql_secure_installation
```

7. Configure DB FreeRadius:
OBS: Change variable <password>
```
mysql -u root -e "CREATE DATABASE phpipam;"
mysql -u root -e "GRANT ALL PRIVILEGES ON phpipam.* TO 'phpipam'@'localhost' IDENTIFIED BY '<password>'";
mysql -u root -e "FLUSH PRIVILEGES";
```

8. Configure security Apache
```
a2enmod rewrite
```
```
nano /etc/apache2/conf-enabled/security.conf
```
```
ServerTokens Prod  
ServerSignature Off
```

9. Configure security PHP
```
nano /etc/php/8.2/cli/php.ini
```
```
expose_php = Off  
```

10. Remove default HTML Page
```
rm -rf /usr/share/apache2/default-site/* /var/www/html/*
```

11. Configure apache2:
```
nano /etc/apache2/sites-enabled/000-default.conf
```
```
<Directory  /var/www/html/>
	Options Indexes FollowSymLinks
	AllowOverride All
</Directory>
```

12. Install phpipam edited flaviojunior1995:
```
cd /tmp
apt install unzip
wget https://github.com/flaviojunior1995/phpipam/releases/download/v1.6.0_v1/phpipam-v1.6.0_edited-v1.zip
unzip phpipam-v1.6.0_edited-v1.zip
cp -R phpipam/* /var/www/html/
```

13. Configure phpIPAM config:
```
nano /var/www/html/config.php
```
```
$db['host']  =  "localhost";
$db['user']  =  "phpipam";
$db['pass']  =  "<password>";
$db['name']  =  "phpipam";
```

14. Import DataBase phpIPAM:
```
mysql -u phpipam -p phpipam < /var/www/html/db/SCHEMA.sql
```

15. Enable and restart services:
```
systemctl  enable  mariadb
systemctl  enable apache2
systemctl  restart  mariadb
systemctl  restart apache2
```

16. Configure crontab for pingCheck and discoveryCheck:
```
crontab -e
```
```
*/15 * * * * /usr/bin/php /var/www/html/functions/scripts/pingCheck.php
*/15 * * * * /usr/bin/php /var/www/html/functions/scripts/discoveryCheck.php
```