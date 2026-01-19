#!/bin/sh

# Crée le dossier si nécessaire et fixe les permissions
#mkdir -p /var/www/var
#cd /var/www/
chown -R www-data:www-data /var/www/briefcase/var

rm -rf /var/www/briefcase/var/cache/*
# Install dépendances
#echo COMPOSERRRRRR
composer install

# Lance la commande d'origine (PHP-FPM)
exec "$@"