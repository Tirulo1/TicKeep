FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx libpng-dev libzip-dev zip curl-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p /run/nginx

RUN printf 'events {}\nhttp {\n    include mime.types;\n    fastcgi_read_timeout 300;\n    server {\n        listen 80;\n        root /var/www/html;\n        index index.php;\n        location / {\n            try_files $uri $uri/ /index.php?$query_string;\n        }\n        location ~ \\.php$ {\n            fastcgi_pass 127.0.0.1:9000;\n            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n            fastcgi_read_timeout 300;\n            include fastcgi_params;\n        }\n    }\n}\n' > /etc/nginx/nginx.conf

RUN echo "request_terminate_timeout = 300" >> /usr/local/etc/php-fpm.d/www.conf

RUN printf '#!/bin/sh\nphp-fpm -D\nnginx -g "daemon off;"\n' > /start.sh && chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]