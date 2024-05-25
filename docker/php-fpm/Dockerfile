FROM php:8.3-fpm
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev
WORKDIR "/app"
RUN docker-php-ext-install pdo pdo_mysql
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
EXPOSE 9000
CMD ["php-fpm"]
