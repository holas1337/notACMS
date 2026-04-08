ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}

RUN apk add --no-cache \
    icu-dev \
    icu-libs \
    imagemagick \
    libwebp-tools \
    nodejs \
    npm \
    unzip \
    git

RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 9000
CMD ["php-fpm"]
