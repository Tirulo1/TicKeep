FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql mysqli gd zip \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80