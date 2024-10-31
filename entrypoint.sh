#!/bin/sh
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
php bin/console d:m:m
php bin/console d:f:l --no-interaction
php-fpm &
php bin/console messenger:consume async