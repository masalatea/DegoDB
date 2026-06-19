FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        apache2 \
        ca-certificates \
        composer \
        curl \
        default-mysql-client \
        git \
        gnupg \
        software-properties-common \
        unzip \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libapache2-mod-php8.4 \
        php8.4 \
        php8.4-cli \
        php8.4-common \
        php8.4-mbstring \
        php8.4-mysql \
        php8.4-sqlite3 \
        php8.4-xml \
        phpunit \
    && a2enmod rewrite \
    && sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && printf "ServerName localhost\n" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock /var/www/
RUN cd /var/www \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

WORKDIR /var/www/html

EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
