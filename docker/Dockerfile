ARG PHP_VERSION
ARG VARIANT
FROM php:${PHP_VERSION}-${VARIANT}-alpine as base

ENV APP_DIRECTORY=/var/www/html
ENV POSTGRES_VERSION=18

WORKDIR ${APP_DIRECTORY}

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN set -eux \
    && apk --update add --no-cache --purge \
        libcap \
        su-exec \
        shadow \
        busybox \
        nodejs \
        npm \
        yarn \
        mysql-client \
        postgresql-client \
        curl \
    && install-php-extensions \
        @composer \
        bcmath \
        intl \
        pcntl \
        posix \
        mysqli pdo_mysql \
        pgsql pdo_pgsql \
        redis \
        swoole \
        xdebug


RUN setcap "cap_net_bind_service=+ep" /usr/local/bin/php

COPY shared/entrypoint.sh /usr/local/bin/entrypoint
COPY shared/start-server.sh /usr/local/bin/start-server
RUN chmod +x /usr/local/bin/entrypoint /usr/local/bin/start-server

COPY shared/php.ini /usr/local/etc/php/conf.d/php.ini

COPY shared/crontab /var/spool/cron/crontabs/root

RUN useradd -ms /bin/sh -u 1337 -U laravel
RUN npm config set registry https://registry.yarnpkg.com/
RUN npm install -g pnpm bun nrm chokidar --registry=https://registry.yarnpkg.com/

RUN sed -i \
    's/artisan serve/artisan octane:start --server=swoole --watch/' \
    /usr/local/bin/start-server

EXPOSE 8080

ENTRYPOINT ["entrypoint"]
