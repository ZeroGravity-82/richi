FROM php:7.2-fpm

RUN apt-get update \
    # extension required for MySQL
    && docker-php-ext-install pdo_mysql

WORKDIR /app
