FROM php:8.2-cli

# Instalar dependências
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Instalar dependências do projeto (roda só na build)
COPY composer.* ./
RUN composer install --no-interaction --prefer-dist
