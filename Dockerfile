FROM node:16 as npm-build

COPY resources /src/resources
COPY package*.json /src/
COPY vite.* /src

WORKDIR /src

RUN npm ci && \
    npm run build

FROM php:8.2-apache-bullseye

RUN apt-get update -y && apt-get upgrade -y

RUN apt-get install --no-install-recommends -y libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libonig-dev libpq-dev && \
    docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql gd zip mbstring exif pcntl bcmath && \
    a2enmod rewrite  && \
    apt-get autoclean -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /tmp/pear/

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
RUN echo 'max_execution_time = 120' >> /usr/local/etc/php/conf.d/docker-php-maxexectime.ini;

COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY --chown=www-data:www-data . /var/www/html
COPY --from=npm-build /src/public/build /var/www/html/public/build

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

COPY .env.prod /var/www/html/.env

RUN php artisan optimize && \
    php artisan config:clear && \
    php artisan storage:link

CMD ["/bin/sh", "-c", "php artisan key:generate -n && php artisan config:cache && php artisan migrate --force && apache2-foreground"]
