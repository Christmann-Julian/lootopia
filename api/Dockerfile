FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    intl \
    zip \
    gd \
    opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /var/www

# Installation du Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

EXPOSE 8000

# Démarrage du serveur Symfony
CMD ["sh", "-c", "composer install --no-interaction --optimize-autoloader && chown -R www-data:www-data /var/www/var && symfony server:start --no-tls --port=8000 --allow-http --allow-all-ip"]