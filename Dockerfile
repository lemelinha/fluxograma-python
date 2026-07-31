FROM php:8.5.8-fpm-alpine3.23

RUN apk add --no-cache \
    libpq-dev \
    libzip-dev \
    zip \
    unzip 

RUN docker-php-ext-install \
    bcmath \
    pcntl \
    pdo_pgsql \
    zip \
    exif

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS pcre-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/api/

COPY src/composer.json src/composer.lock .
RUN composer install --no-scripts --no-autoloader

COPY --chown=www-data:www-data ./src .
RUN composer install -o

RUN chown -R www-data:www-data /var/www/api/ && \
    chmod -R 775 /var/www/api/storage /var/www/api/bootstrap/cache

EXPOSE 9000

USER www-data

CMD ["php-fpm"]
