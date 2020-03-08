# See https://github.com/docker-library/php/blob/master/7.1/fpm/Dockerfile
FROM php:7.3-fpm
ARG TIMEZONE

MAINTAINER Andrzej Nowicki <andrzej@softnauts.com>

RUN apt-get update && apt-get install -y \
    sudo \
    procps \
    openssl \
    git \
    zlib1g-dev \
    unzip \
    libicu-dev \
    libxml2-dev \
    libxslt-dev \
    g++ \
    curl \
    htop \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libpng-dev \
    zlib1g-dev \
    iputils-ping \
    libjpeg-dev

RUN docker-php-ext-configure gd \
    --with-jpeg-dir=/usr/include/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Set packagist
RUN composer config --global repo.packagist composer https://packagist.org

# Enable parallel installs
RUN composer global require hirak/prestissimo

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini
RUN "date"

# Update /etc/hosts
RUN echo "127.0.0.1 localunixsocket" >> /etc/hosts
RUN echo "1144.217.203.53 packagist.com" >> /etc/hosts

# Set max uploaded file size
RUN echo "upload_max_filesize = 40M" >> /usr/local/etc/php/conf.d/file-upload.ini
RUN echo "post_max_size = 40M" >> /usr/local/etc/php/conf.d/file-upload.ini

# Change php settings
ADD php.ini /usr/local/etc/php

# Type docker-php-ext-install to see available extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install xdebug
RUN pecl install xdebug

# Install nodejs and
RUN apt-get install -y gnupg2
RUN rm -rf /var/lib/apt/lists/ && curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install nodejs -y
RUN npm install -g yarn

# Turn off xdebug as default
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.off

# Add global functions for turn on/off xdebug
RUN echo "mv /usr/local/etc/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.off && sudo pkill -o -USR2 php-fpm" > /usr/bin/xoff && chmod +x /usr/bin/xoff \
    && echo "mv /usr/local/etc/php/conf.d/xdebug.off /usr/local/etc/php/conf.d/xdebug.ini && sudo pkill -o -USR2 php-fpm" > /usr/bin/xon && chmod +x /usr/bin/xon

WORKDIR /var/www/symfony
