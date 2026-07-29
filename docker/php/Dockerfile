FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    pkg-config \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd \
        --with-freetype=/usr/include/freetype2 \
        --with-jpeg=/usr/include \
        --with-webp=/usr/include \
    && docker-php-ext-install \
        pdo_mysql mysqli gd zip mbstring curl opcache intl xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY custom.ini /usr/local/etc/php/conf.d/custom.ini