#!/bin/sh

# Crée le dossier si nécessaire et fixe les permissions
#mkdir -p /var/www/var
#cd /var/www/
chown -R www-data:www-data /var/www/var
# Install dépendances
#echo COMPOSERRRRRR
#composer install --no-dev --optimize-autoloader --no-scripts

# Lance la commande d'origine (PHP-FPM)
exec "$@"