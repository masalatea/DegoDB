FROM php:8.4-cli

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        firebird-dev \
    && docker-php-ext-install pdo_firebird pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /workspace
