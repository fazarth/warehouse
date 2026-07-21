FROM php:8.2-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Prefer production install; allow lockfile if present
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && php -r "file_exists('storage/logs') || mkdir('storage/logs', 0775, true);" \
    && php -r "file_exists('bootstrap/cache') || mkdir('bootstrap/cache', 0775, true);" \
    && chmod -R ug+rwx storage bootstrap/cache

ENV APP_ENV=production
ENV LOG_CHANNEL=stderr
ENV PORT=8000

EXPOSE 8000

COPY docker/start-wms.sh /usr/local/bin/start-wms.sh
RUN chmod +x /usr/local/bin/start-wms.sh

CMD ["/usr/local/bin/start-wms.sh"]
