FROM php:8.2-cli

# ── Dépendances système ──────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libaio1t64 \
    && docker-php-ext-install pdo mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Lien symbolique libaio (protégé si déjà existant)
RUN ln -sf /usr/lib/x86_64-linux-gnu/libaio.so.1t64 \
           /usr/lib/x86_64-linux-gnu/libaio.so.1

# ── Node.js 22 ───────────────────────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Oracle Instant Client ─────────────────────────────────────────────────────
COPY ./oracle/instantclient-basiclite-linux.x64-21.21.0.0.0dbru.zip /tmp/
COPY ./oracle/instantclient-sdk-linux.x64-21.21.0.0.0dbru.zip       /tmp/

RUN mkdir -p /opt/oracle \
    && unzip -o /tmp/instantclient-basiclite-linux.x64-21.21.0.0.0dbru.zip \
              -d /opt/oracle \
    && unzip -o /tmp/instantclient-sdk-linux.x64-21.21.0.0.0dbru.zip \
              -d /opt/oracle \
    && rm /tmp/instantclient-*.zip \
    && echo /opt/oracle/instantclient_21_21 \
         > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

ENV ORACLE_HOME=/opt/oracle/instantclient_21_21
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_21

# ── Extensions PHP Oracle ─────────────────────────────────────────────────────
RUN echo "instantclient,/opt/oracle/instantclient_21_21" | pecl install oci8 \
    && docker-php-ext-enable oci8

# Version corrigée : 21.21 → correspond à ton Instant Client
RUN docker-php-ext-configure pdo_oci \
        --with-pdo-oci=instantclient,/opt/oracle/instantclient_21_21,21.21 \
    && docker-php-ext-install pdo_oci

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copier composer.json/lock EN PREMIER → cache Docker préservé
COPY composer.json composer.lock ./
RUN composer install \
    --optimize-autoloader \
    --no-interaction \
    --no-dev \
    --no-scripts \
    --ignore-platform-req=ext-oci8 \
    --ignore-platform-req=ext-pdo_oci

# Copier package.json EN PREMIER → cache Docker préservé pour npm
COPY package.json package-lock.json ./
RUN npm ci

# Copier le reste du projet
COPY . .

# ── Assets ────────────────────────────────────────────────────────────────────
RUN npm run build

# ── Permissions ───────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini
CMD ["/start.sh"]