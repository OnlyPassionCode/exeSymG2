FROM php:8.3-fpm-alpine

# Installer les extensions PDO et MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Installer APCu via PECL
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del $PHPIZE_DEPS

# Pas besoin de Xdebug en mode production

# Installer Xdebug avec autoconf et linux-headers
# RUN apk add --no-cache autoconf linux-headers \
    # && pecl install xdebug \
    # && docker-php-ext-enable xdebug \
    # && apk del autoconf linux-headers

# Configurer Xdebug
# RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    # && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    # && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    # && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    # && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Activer et configurer OPcache
RUN docker-php-ext-install opcache \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

RUN apk --no-cache add icu-dev && \
    docker-php-ext-install intl

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /var/www/html

COPY entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

CMD ["sh", "/usr/local/bin/entrypoint.sh"]