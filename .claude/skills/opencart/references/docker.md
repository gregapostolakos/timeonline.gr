# Docker Reference — OpenCart Dev Environments

## Standard docker-compose.yml (PHP 8.3)

```yaml
services:
  php:
    build:
      context: ./docker/php
      dockerfile: Dockerfile
    container_name: ${COMPOSE_PROJECT_NAME}_php
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    networks:
      - opencart

  nginx:
    image: nginx:alpine
    container_name: ${COMPOSE_PROJECT_NAME}_nginx
    ports:
      - "${NGINX_PORT:-8000}:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - opencart

  db:
    image: mariadb:10.11
    container_name: ${COMPOSE_PROJECT_NAME}_db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASS}
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - opencart

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: ${COMPOSE_PROJECT_NAME}_pma
    ports:
      - "${PMA_PORT:-8080}:80"
    environment:
      PMA_HOST: db
      PMA_USER: ${DB_USER}
      PMA_PASSWORD: ${DB_PASS}
    depends_on:
      - db
    networks:
      - opencart

networks:
  opencart:

volumes:
  db_data:
```

> **Σημείωση:** Το php service χρησιμοποιεί πάντα `build` (όχι `image`) γιατί χρειάζεται custom extensions. Το `custom.ini` γίνεται `COPY` μέσα στο Dockerfile — δεν γίνεται mount ως volume.

---

## PHP Version Variants

Για PHP 8.2, αλλάζεις μόνο το `FROM` στο Dockerfile:

```dockerfile
FROM php:8.2-fpm
```

Για legacy PHP 7.4:
```dockerfile
FROM php:7.4-fpm
```

Για legacy PHP 7.3:
```dockerfile
FROM php:7.3-fpm
```

Το υπόλοιπο Dockerfile μένει ίδιο για όλες τις εκδόσεις.

---

## docker/php/Dockerfile

```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
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
    && docker-php-ext-install \
        pdo_mysql mysqli gd zip mbstring curl opcache intl xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY custom.ini /usr/local/etc/php/conf.d/custom.ini
```

> **Σημαντικό:** Χρειάζονται και τα `libcurl4-openssl-dev` και `libicu-dev` — χωρίς αυτά τα `curl` και `intl` extensions αποτυγχάνουν κατά το build.

---

## docker/php/custom.ini

```ini
upload_max_filesize = 256M
post_max_size = 256M
memory_limit = 256M
max_execution_time = 300
display_errors = On
error_reporting = E_ALL
```

---

## docker/nginx/default.conf

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html;
    index index.php;
    client_max_body_size 256M;

    # OpenCart SEO URLs
    location / {
        try_files $uri $uri/ @opencart;
    }

    location @opencart {
        rewrite ^/(.+)$ /index.php?_route_=$1 last;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block access to sensitive files
    location ~ /\. { deny all; }
    location ~* \.(git|sql|bak)$ { deny all; }
    location = /config.php { deny all; }
    location = /admin/config.php { deny all; }
}
```

---

## .env.example (commit αυτό, όχι το .env)

```env
COMPOSE_PROJECT_NAME=mysite
NGINX_PORT=8000
PMA_PORT=8080
DB_NAME=opencart
DB_USER=opencart
DB_PASS=secret
PHP_VERSION=8.3
```

> **Ports:** NGINX default `8000` (όχι 80) — site: `http://localhost:8000`, phpMyAdmin: `http://localhost:8080`

---

## config.php για Docker

Μετά το `docker compose up`, ορίζεις το `config.php`:

```php
<?php
// HTTP
define('HTTP_SERVER', 'http://localhost:8000/');
define('HTTP_CATALOG', 'http://localhost:8000/');

// HTTPS — same as HTTP for local
define('HTTPS_SERVER', 'http://localhost:8000/');
define('HTTPS_CATALOG', 'http://localhost:8000/');

// DIR
define('DIR_APPLICATION', '/var/www/html/catalog/');
define('DIR_SYSTEM', '/var/www/html/system/');
define('DIR_IMAGE', '/var/www/html/image/');
define('DIR_STORAGE', '/var/www/html/system/storage/');
define('DIR_LANGUAGE', '/var/www/html/catalog/language/');
define('DIR_TEMPLATE', '/var/www/html/catalog/view/theme/');
define('DIR_CONFIG', '/var/www/html/system/config/');
define('DIR_CACHE', '/var/www/html/system/storage/cache/');
define('DIR_DOWNLOAD', '/var/www/html/system/storage/download/');
define('DIR_LOGS', '/var/www/html/system/storage/logs/');
define('DIR_MODIFICATION', '/var/www/html/system/storage/modification/');
define('DIR_UPLOAD', '/var/www/html/system/storage/upload/');
define('DIR_CATALOG', '/var/www/html/catalog/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'db');          // Docker service name
define('DB_USERNAME', 'opencart');
define('DB_PASSWORD', 'secret');
define('DB_DATABASE', 'opencart');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
```

Για το `admin/config.php` ίδιο pattern + τα extra `DIR_` constants που χρειάζεται το admin (π.χ. `DIR_CATALOG` pointing to `catalog/`).

---

## Useful Docker Commands

```bash
# Start
docker compose up -d

# Stop
docker compose down

# View PHP logs
docker compose logs -f php

# Access PHP container shell
docker compose exec php bash

# Import DB dump
docker compose exec -T db mariadb -u opencart -psecret opencart < dump.sql

# Export DB
docker compose exec db mariadb-dump -u opencart -psecret opencart > dump.sql

# Clear OC cache from host
docker compose exec php rm -rf /var/www/html/system/storage/cache/*

# Rebuild PHP image (μετά από αλλαγές στο Dockerfile)
docker compose build php
docker compose up -d
```

---

## Directory Structure

```
project-root/
├── docker/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── custom.ini
│   └── nginx/
│       └── default.conf
├── docker-compose.yml
├── .env              ← δεν commiteύεται
├── .env.example      ← commiteύεται
└── ... (OpenCart files)
```
