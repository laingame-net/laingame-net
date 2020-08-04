#FROM php:fpm-alpine
FROM php:7.2-fpm-alpine
RUN apk add --no-cache zip libzip-dev
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install pdo_mysql zip

#Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=. --filename=composer
RUN mv composer /usr/local/bin/
#RUN composer dump-autoload -d /var/www/laingame.net

CMD ["php-fpm"]
EXPOSE 9000
