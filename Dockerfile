# syntax=docker/dockerfile:1
# Una sola fuente, dos imágenes:
#   docker build --target app -t colfe-web-app .    (php-fpm con el código)
#   docker build --target web -t colfe-web-nginx .  (nginx con los estáticos de public/)
# Se usa Debian (glibc) y no Alpine: recibo.php necesita iconv con //TRANSLIT, que musl no soporta.

############################ app: PHP-FPM ############################
FROM php:8.4-fpm-bookworm AS app

RUN set -eux; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql opcache; \
    rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-colfe.ini

WORKDIR /var/www/html
COPY --chown=www-data:www-data config ./config
COPY --chown=www-data:www-data src ./src
COPY --chown=www-data:www-data public ./public
# Herramienta de línea de comandos para crear usuarios (no es accesible por HTTP)
COPY --chown=www-data:www-data db/tools/crear_usuario.php ./db/tools/crear_usuario.php

# Logs fuera de la raíz web, escribibles por php-fpm
RUN mkdir -p storage/logs && chown -R www-data:www-data storage

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

############################ web: nginx ############################
FROM nginx:1.27-alpine AS web

# nginx sustituye las variables ${...} de /etc/nginx/templates/*.template al arrancar
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY public /var/www/html/public

EXPOSE 80
