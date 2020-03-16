FROM php:fpm-alpine
RUN docker-php-ext-install pdo_mysql

#Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=. --filename=composer
RUN mv composer /usr/local/bin/
#RUN composer dump-autoload -d /var/www/laingame.net

CMD ["php-fpm"]
EXPOSE 9000
