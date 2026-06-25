FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . .

RUN mkdir -p backend/uploads && chmod 775 backend/uploads

ENTRYPOINT ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} router.php"]
