FROM php:8.2-fpm

# Dépendances système
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev libaio1t64 \
    && docker-php-ext-install pdo mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Lien symbolique pour libaio
RUN ln -s /usr/lib/x86_64-linux-gnu/libaio.so.1t64 /usr/lib/x86_64-linux-gnu/libaio.so.1

# Node.js 22 (corrigé depuis 18)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Oracle Instant Client — Basic Light + SDK
COPY ./oracle/instantclient-basiclite-linux.x64-21.21.0.0.0dbru.zip /tmp/
COPY ./oracle/instantclient-sdk-linux.x64-21.21.0.0.0dbru.zip /tmp/

RUN mkdir -p /opt/oracle \
    && unzip -o /tmp/instantclient-basiclite-linux.x64-21.21.0.0.0dbru.zip -d /opt/oracle \
    && unzip -o /tmp/instantclient-sdk-linux.x64-21.21.0.0.0dbru.zip -d /opt/oracle \
    && rm /tmp/instantclient-basiclite-linux.x64-21.21.0.0.0dbru.zip \
    && rm /tmp/instantclient-sdk-linux.x64-21.21.0.0.0dbru.zip \
    && echo /opt/oracle/instantclient_21_21 > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

# Variables Oracle
ENV ORACLE_HOME=/opt/oracle/instantclient_21_21
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_21

# OCI8
RUN echo "instantclient,/opt/oracle/instantclient_21_21" | pecl install oci8 \
    && docker-php-ext-enable oci8

# PDO_OCI
RUN docker-php-ext-configure pdo_oci \
    --with-pdo-oci=instantclient,/opt/oracle/instantclient_21_21,21.1 \
    && docker-php-ext-install pdo_oci

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Composer install
RUN composer install --optimize-autoloader --no-interaction \
    --ignore-platform-req=ext-oci8 \
    --ignore-platform-req=ext-pdo_oci

# Build assets
RUN npm install && npm run build

# Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]