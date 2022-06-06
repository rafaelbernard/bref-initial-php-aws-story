ARG NODE_VERSION=16
ARG PHP_VERSION=8.1

FROM node:${NODE_VERSION} AS node_base

# Image
FROM php:${PHP_VERSION} AS base-env

COPY --from=node_base /usr/local /usr/local

RUN apt-get update \
    && apt-get install -y \
        unzip wget \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app/php

# Composer
RUN wget https://getcomposer.org/installer \
    && php ./installer && rm installer \
    && mv composer.phar /usr/local/bin/composer

# Symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv ~/.symfony/bin/symfony /usr/local/bin/symfony

# Install and enable symfony on HTTPS
RUN symfony server:ca:install

WORKDIR /app/php

COPY php/composer.json php/composer.lock symfony.lock ./
RUN composer install --no-scripts

WORKDIR /app

RUN npm install -g cdk

# Copy source
COPY . .
RUN cd php && composer install

WORKDIR /tmp
RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip" \
    && unzip awscliv2.zip \
    && ./aws/install \
    && rm awscliv2.zip

WORKDIR /app/php

# Exposing
EXPOSE 8000

CMD ["symfony","serve","--port=8000"]
