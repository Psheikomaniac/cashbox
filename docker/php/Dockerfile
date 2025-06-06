# docker/php/Dockerfile
# Build-Stage
FROM php:8.4-fpm-alpine AS php_build

# Installiere Abhängigkeiten
RUN apk update && \
    apk add --no-cache \
        git \
        unzip \
        libpq-dev \
        postgresql-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        zlib-dev \
        libzip-dev \
        curl \
        bash

# PHP-Erweiterungen installieren
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        gd

# Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Produktions-Stage
FROM php:8.4-fpm-alpine AS php_prod

# Produktionsabhängigkeiten installieren
RUN apk update && \
    apk add --no-cache \
        libpq \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        curl \
        bash

# PHP-Erweiterungen von der Build-Stage kopieren
COPY --from=php_build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php_build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Composer von der Build-Stage kopieren
COPY --from=php_build /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis einrichten
WORKDIR /var/www

# Kopiere Projektdateien
COPY . /var/www/

# Erstelle den entrypoint als ausführbare Datei
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Umgebungsvariablen setzen
ENV APP_ENV=prod

# Gesundheitscheck-Skript kopieren
COPY docker/php/health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/health-check

# Entrypoint und Standardbefehl festlegen
ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# Entwicklungs-Stage
FROM php_build AS php_dev

# Entwicklungstools installieren
RUN apk add --no-cache \
    sqlite \
    vim \
    nano

# Xdebug installieren
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Arbeitsverzeichnis einrichten
WORKDIR /var/www

# Kopiere Projektdateien
COPY . /var/www/

# Erstelle den entrypoint als ausführbare Datei
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Kopiere Xdebug-Konfiguration
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Gesundheitscheck-Skript kopieren
COPY docker/php/health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/health-check

# Umgebungsvariablen setzen
ENV APP_ENV=dev

# Entrypoint und Standardbefehl festlegen
ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
