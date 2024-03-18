FROM --platform=linux/amd64 php:8.2.12-fpm
LABEL maintainer=devs@astrotech.solutions \
      vendor="Astrotech Software House"

ENV TIMEZONE="America/Sao_Paulo"

RUN apt-get update && \
    apt-get install -y \
        wget \
        git \
        zip \
        nano \
        zlib1g-dev \
        libzip-dev \
        unzip

RUN docker-php-ext-install zip

RUN cp /usr/share/zoneinfo/$TIMEZONE /etc/localtime && \
    echo $TIMEZONE > /etc/timezone

RUN wget -O phpunit https://phar.phpunit.de/phpunit.phar && \
    chmod +x phpunit && \
    mv phpunit /usr/local/bin/phpunit

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer self-update

RUN touch /var/log/xdebug.log
RUN pecl install xdebug-3.2.2 && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

EXPOSE 9000 8000

WORKDIR /app

CMD ["php", "-S", "0.0.0.0:8000"]
