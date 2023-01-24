FROM php:7.2-cli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.26 --quiet
RUN apt-get update && apt-get install -y unzip \
    # extension required for MySQL
    && docker-php-ext-install pdo_mysql

# Install Symfony CLI
RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | tee /etc/apt/sources.list.d/symfony-cli.list \
    && apt-get update && apt-get install -y symfony-cli

# Clear APT cache
RUN apt-get clean && rm -rf /var/lib/apt/lists

# PHP configuration
COPY conf.d /usr/local/etc/php/conf.d

WORKDIR /app
