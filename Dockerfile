FROM php:7.4-cli

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN apt-get update && apt-get upgrade -y \
    netcat \
    git \
    libzip-dev \
    unzip

RUN install-php-extensions \
    iconv \
    mbstring \
    zip \
    bcmath \
    pdo_mysql \
    mysqli

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

COPY . /app

RUN groupadd -r user && useradd -m -g user user

RUN chown -R user /app

USER user

RUN sh /app/bin/build.sh

ENTRYPOINT ["sh", "/app/bin/run.sh"]
