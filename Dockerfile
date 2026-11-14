FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite

COPY docker/apache-sigis.conf /etc/apache2/conf-enabled/apache-sigis.conf
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/php-uploads.ini
COPY docker/php-errors.ini /usr/local/etc/php/conf.d/php-errors.ini

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads

WORKDIR /var/www/html
