FROM php:8.4-apache

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html

RUN printf '%s\n' '<FilesMatch "^\.">' '    Require all denied' '</FilesMatch>' \
    > /etc/apache2/conf-available/deny-dotfiles.conf \
    && a2enconf deny-dotfiles
