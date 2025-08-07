FROM php:8.2-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Устанавливаем Redis-расширение для PHP (клиент)
RUN pecl install redis && docker-php-ext-enable redis

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы проекта
COPY . /var/www/html/
WORKDIR /var/www/html

# Настраиваем Apache
RUN a2enmod rewrite
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf