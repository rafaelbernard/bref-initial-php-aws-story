# syntax=docker/dockerfile:1.4
ARG NODE_VERSION=16
ARG PHP_VERSION=8.1

FROM node:${NODE_VERSION}-alpine AS node-base

FROM php:${PHP_VERSION}-alpine AS base-env

COPY --from=node-base /usr/local /usr/local

RUN apk add --no-cache \
    libstdc++

WORKDIR /tmp
RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip" \
    && unzip awscliv2.zip \
    && ./aws/install \
    && rm awscliv2.zip

RUN npm install -g serverless cdk

RUN wget https://getcomposer.org/installer \
    && php ./installer && rm installer \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /tmp
COPY _build-image/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh

WORKDIR /app/php

COPY php/composer.json php/composer.lock ./
RUN composer install --no-scripts

# Copy source
WORKDIR /app
COPY . .

CMD ["/usr/local/bin/run.sh"]
