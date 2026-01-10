FROM php:8.4-fpm-alpine

# Установка системных зависимостей
RUN apk add --no-cache --update \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    openssl-dev \
    linux-headers \
    $PHPIZE_DEPS

# Установка PHP расширений (ctype, xml, iconv уже включены в PHP 8.4 по умолчанию)
RUN set -eux; \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        opcache \
        mbstring \
    && docker-php-source delete \
    && apk del --no-network $PHPIZE_DEPS \
    && rm -rf /tmp/* /var/cache/apk/*

# Установка Xdebug (требует PHPIZE_DEPS)
RUN apk add --no-cache --virtual .xdebug-build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --no-network .xdebug-build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www/html

# Создание рабочей директории
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html

# Копирование конфигурации PHP-FPM (конфиг не монтируется, так как редко меняется)
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Открытие порта для PHP-FPM
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"]

