# syntax=docker/dockerfile:1
FROM php:8.3-cli as final
WORKDIR /app
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Your PHP application may require additional PHP extensions to be installed
# manually. For detailed instructions for installing extensions can be found, see
# https://github.com/docker-library/docs/tree/master/php#how-to-install-more-php-extensions
# The following code blocks provide examples that you can edit and use.
#
# Add core PHP extensions, see
# https://github.com/docker-library/docs/tree/master/php#php-core-extensions
# This example adds the apt packages for the 'gd' extension's dependencies and then
# installs the 'gd' extension. For additional tips on running apt-get:
# https://docs.docker.com/go/dockerfile-aptget-best-practices/
 RUN apt-get update && apt-get install -y \
     libzip-dev \
     librdkafka-dev \
     && docker-php-ext-install zip
#
# Add PECL extensions, see
# https://github.com/docker-library/docs/tree/master/php#pecl-extensions
# This example adds the 'redis' and 'xdebug' extensions.
 RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

# Use the default production configuration for PHP runtime arguments, see
# https://github.com/docker-library/docs/tree/master/php#configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
CMD ["sleep", "infinity"]
