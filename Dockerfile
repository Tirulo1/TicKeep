FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apk add --no-cache nginx libpng-dev zip curl-dev \
    && docker-php-ext-install gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p /run/nginx

COPY <<'EOF' /etc/nginx/nginx.conf
events {}
http {
    include mime.types;
    server {
        listen 80;
        root /var/www/html;
        index index.php;
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
EOF

COPY <<'EOF' /start.sh
#!/bin/sh
php-fpm -D
nginx -g "daemon off;"
EOF

RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]