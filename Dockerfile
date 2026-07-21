FROM node:20-bookworm AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY vite.config.js postcss.config.js* tailwind.config.js* ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM php:8.2-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libfreetype6-dev libjpeg62-turbo-dev \
    libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app
COPY --from=assets /app/public/build /app/public/build

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && php -r "file_exists('storage/logs') || mkdir('storage/logs', 0775, true);" \
    && php -r "file_exists('bootstrap/cache') || mkdir('bootstrap/cache', 0775, true);" \
    && chmod -R ug+rwx storage bootstrap/cache \
    && sed -i 's/\r$//' /app/docker/start-wms.sh \
    && chmod +x /app/docker/start-wms.sh \
    && cp /app/docker/start-wms.sh /usr/local/bin/start-wms.sh \
    && test -f /app/public/build/manifest.json

ENV APP_ENV=production
ENV LOG_CHANNEL=stderr
ENV PORT=8080

EXPOSE 8080

CMD ["/usr/local/bin/start-wms.sh"]
