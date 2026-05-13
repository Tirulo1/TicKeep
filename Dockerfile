FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt-get update && apt-get install -y libpng-dev libzip-dev zip curl && \
    docker-php-ext-install gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite

EXPOSE 80