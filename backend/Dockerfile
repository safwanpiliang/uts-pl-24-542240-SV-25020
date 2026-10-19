# Versi dipin eksplisit (bukan latest) supaya build selalu sama.
FROM php:8.4.26-cli-alpine3.24

# Ekstensi PHP untuk PostgreSQL
RUN apk add --no-cache libpq postgresql-dev \
    && docker-php-ext-install pdo_pgsql \
    && apk del --no-cache postgresql-dev

COPY --from=composer:2.8.12 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1) Salin hanya composer.json dan composer.lock, lalu pasang dependensi.
#    Layer ini hanya dibangun ulang kalau dua file itu berubah, bukan tiap
#    kali kode aplikasi berubah (cache lapisan Docker).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist \
    --no-scripts --no-autoloader

# 2) Baru salin kode aplikasi.
COPY . .

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

USER www-data

EXPOSE 8000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
