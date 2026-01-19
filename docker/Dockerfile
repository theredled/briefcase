FROM php:8.4-fpm

# Extensions PHP nécessaires pour Symfony
RUN apt-get update && apt-get install -y git unzip libzip-dev libicu-dev
RUN docker-php-ext-install pdo pdo_mysql zip opcache intl ctype fileinfo iconv


# Répertoire de travail
WORKDIR /var/www/briefcase

# Copie les fichiers
#COPY . .

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#COPY composer.json composer.lock ./
#-- Prod
#RUN composer install --no-dev --optimize-autoloader --no-scripts
#-- Dev
#RUN composer install

# xdebug
COPY docker/docker-php-ext-disable-xdebug.sh /usr/local/bin/docker-php-ext-disable-xdebug.sh
RUN chmod +x /usr/local/bin/docker-php-ext-disable-xdebug.sh
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY docker/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Données initiales
COPY ./var/data.db /var/www/briefcase/var/data.db
COPY ./var/downloadable_files  /var/www/briefcase/var/downloadable_files
COPY ./var/tests /var/www/briefcase/var/tests
COPY ./public/dl /var/www/briefcase/public/dl


# Permissions
#RUN chown -R www-data:www-data /var/www/var

# Entrypoint : pour agir sur un volumé monté
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Port
EXPOSE 9000

CMD ["php-fpm"]