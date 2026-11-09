FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install pdo_mysql zip bcmath \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Apuntar DocumentRoot de Apache a /var/www/html/public para Laravel 12
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . /var/www/html

# Script de arranque automático para clonación limpia desde GitHub
RUN printf '#!/bin/sh\n\
set -e\n\
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache\n\
if [ ! -f vendor/autoload.php ]; then\n\
    echo "[SGA Docker] Instalando dependencias de Composer..."\n\
    composer install --no-interaction --prefer-dist --optimize-autoloader\n\
fi\n\
if [ ! -f .env ]; then\n\
    echo "[SGA Docker] Generando archivo .env inicial..."\n\
    printf "APP_NAME=\"SGA - Gestion de Aprendices\"\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost:8080\nDB_CONNECTION=mysql\nDB_HOST=db\nDB_PORT=3306\nDB_DATABASE=gestion_aprendices\nDB_USERNAME=sga_user\nDB_PASSWORD=sga_secret\nSESSION_DRIVER=file\nCACHE_STORE=file\n" > .env\n\
    php artisan key:generate --force\n\
fi\n\
chown -R www-data:www-data storage bootstrap/cache\n\
chmod -R 775 storage bootstrap/cache\n\
echo "[SGA Docker] Ejecutando migraciones y semillas iniciales..."\n\
php artisan migrate --seed --force\n\
exec apache2-foreground\n' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
