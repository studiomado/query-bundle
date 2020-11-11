FROM studiomado/php:7.1.29-fpm-alpine3.9-base

USER root

RUN apk add --no-cache \
    openssh \
    bash \
    zsh


# install extensions
#  soap
RUN apk add --update --no-cache \
    libxml2-dev \
    libxslt-dev \
    && docker-php-ext-install soap \
    && docker-php-ext-install xsl


#COPY conf/xdebug-starter.sh /usr/local/bin/xdebug-starter
#RUN chmod +x /usr/local/bin/xdebug-starter
#RUN /usr/local/bin/xdebug-starter

USER utente

RUN echo 'alias test="./bin/phpunit --exclude-group slow"' >> /home/utente/.bashrc \
    && echo 'alias test-all="./bin/phpunit"' >> /home/utente/.bashrc \
    && echo 'alias sfcc="rm -Rf var/cache/*"' >> /home/utente/.bashrc
