FROM php:8.2-apache

# 1. Installation des dépendances
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    git \
    libssl-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql intl zip gd

# 2. Installation de l'extension MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# 3. Configuration d'Apache pour Symfony
RUN a2enmod rewrite
RUN a2enmod deflate

# On définit le dossier public comme racine
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# CORRECTIF : On autorise le .htaccess pour les routes Symfony
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# 4. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html