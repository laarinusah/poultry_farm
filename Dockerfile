FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html/

RUN printf '<Directory /var/www/html/>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/poultry_farm.conf

RUN a2enconf poultry_farm

EXPOSE 80