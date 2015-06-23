#!/usr/bin/env bash

# Update
apt-get update
apt-get upgrade

# Install Apache & PHP
apt-get install -y php5
apt-get install -y php5-mysql php5-curl php5-gd php5-intl php-pear php5-imap php5-mcrypt php5-recode php5-xmlrpc php5-xsl php5-fpm
apt-get install -y git

# Install composer
php -r "readfile('https://getcomposer.org/installer');" | php
mv composer.phar /usr/bin/composer

#Create the web root if it does not exist
mkdir -p /var/www

# Mysql
#
# Ignore the post install questions
export DEBIAN_FRONTEND=noninteractive

# Install MySQL quietly
apt-get -q -y install mysql-server-5.5

mysql -u root -e "CREATE DATABASE IF NOT EXISTS sanitizer"
mysql -u root -e "GRANT ALL PRIVILEGES ON vagrant_transient.* TO 'root'@'localhost' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"