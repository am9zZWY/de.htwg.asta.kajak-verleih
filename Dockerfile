FROM php:bullseye
WORKDIR /var/www/html
VOLUME /var/www/html

EXPOSE 80
EXPOSE $PORT

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite

CMD /usr/sbin/apache2ctl -D FOREGROUND