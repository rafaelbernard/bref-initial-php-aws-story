ARG NODE_VERSION=16

FROM node:${NODE_VERSION} AS node_base

# Image
FROM php:8.0 AS base-env

COPY --from=node_base /usr/local /usr/local

RUN apt-get update \
    && apt-get install -y \
        unzip wget \
    && rm -rf /var/lib/apt/lists/*

# Composer
RUN wget https://getcomposer.org/installer \
    && php ./installer && rm installer \
    && mv composer.phar /usr/local/bin/composer

# Symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv ~/.symfony/bin/symfony /usr/local/bin/symfony

# Install and enable symfony on HTTPS
RUN symfony server:ca:install

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts

RUN npm install -g serverless

# Copy source
COPY . .
RUN composer install \
    && serverless plugin install -n serverless-plugin-log-retention

# Exposing
EXPOSE 8000

CMD ["symfony","serve","--port=8000"]
