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

RUN npm install -g serverless cdk

# Copy source
COPY . .
RUN composer install \
    && serverless plugin install -n serverless-plugin-log-retention

WORKDIR /tmp
RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip" \
    && unzip awscliv2.zip \
    && ./aws/install \
    && rm awscliv2.zip

WORKDIR /app

# Exposing
EXPOSE 8000

CMD ["symfony","serve","--port=8000"]
