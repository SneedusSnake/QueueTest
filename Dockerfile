# syntax=docker/dockerfile:1
FROM php:8.3-cli as final
WORKDIR /app
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY ./composer.json ./composer.json

 RUN apt-get update && apt-get install -y \
     libzip-dev \
     librdkafka-dev \
     && docker-php-ext-install zip
 RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN composer install --no-interaction
CMD ["sleep", "infinity"]
