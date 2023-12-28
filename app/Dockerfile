FROM php:latest

ARG MYSQL_DATABASE
ARG MYSQL_ROOT_PASSWORD

RUN docker-php-ext-install mysqli
COPY ./src/ .
