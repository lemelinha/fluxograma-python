FROM php:8.5.8-fpm-alpine3.23

RUN apk add --no-cache \
    libpq-dev \
    libzip-dev 

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS pcre-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear \
    && docker-php-ext-install \
        pcntl \
        pdo_pgsql \
        zip 

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_CACHE_DIR=/tmp/composer-cache

WORKDIR /var/www/api/

COPY src/composer.json src/composer.lock .
RUN composer install --no-dev --no-scripts --no-autoloader \
    && rm -rf /tmp/composer-cache

COPY --chown=www-data:www-data ./src .
RUN composer install -o --no-dev \
    && rm -rf /tmp/composer-cache \
    && chmod -R 775 /var/www/api/storage /var/www/api/bootstrap/cache

EXPOSE 9000

USER www-data

CMD ["php-fpm"]
