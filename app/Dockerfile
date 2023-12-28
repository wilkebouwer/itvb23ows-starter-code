FROM php:8.3.1

ARG MYSQL_DATABASE
ARG MYSQL_ROOT_PASSWORD

RUN docker-php-ext-install mysqli
COPY ./src/ .
