# Server aplikasi: FrankenPHP (Caddy + PHP) menggantikan `php artisan serve`.
# Versi dipin eksplisit (bukan latest) supaya build selalu sama.
FROM dunglas/frankenphp:1.13.0-php8.4.26-alpine

# Ekstensi PHP untuk PostgreSQL
RUN install-php-extensions pdo_pgsql

COPY --from=composer:2.8.12 /usr/bin/composer /usr/bin/composer

# Konfigurasi PHP untuk produksi
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /app

# 1) Salin hanya composer.json dan composer.lock, lalu pasang dependensi.
#    Layer ini hanya dibangun ulang kalau dua file itu berubah, bukan tiap
#    kali kode aplikasi berubah (cache lapisan Docker).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist \
    --no-scripts --no-autoloader

# 2) Baru salin kode aplikasi.
COPY . .

COPY docker/Caddyfile /etc/caddy/Caddyfile

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache /data/caddy /config/caddy \
    && chmod +x docker/entrypoint.sh

USER www-data

EXPOSE 8000

# Healthcheck bawaan image dasar mengetuk Caddy admin API (port 2019) yang
# dimatikan di Caddyfile, jadi diganti dengan endpoint /up milik Laravel.
HEALTHCHECK --interval=10s --timeout=3s --start-period=40s --retries=5 \
  CMD curl -fsS http://localhost:8000/up || exit 1

ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
