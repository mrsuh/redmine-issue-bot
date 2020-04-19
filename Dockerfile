FROM php:7.3-cli

RUN apt-get update && apt-get upgrade -y \
    libzip-dev \
    unzip \
    libmcrypt-dev \
    librabbitmq-dev \
    zlib1g-dev \
    && docker-php-ext-install \
    iconv \
    mbstring \
    zip \
    bcmath \
    pdo_mysql \
    mysqli

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

RUN sh /app/bin/build.sh

ENTRYPOINT ["sh", "/app/bin/run.sh"]