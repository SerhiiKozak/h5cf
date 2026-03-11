#!/bin/sh

set -e
echo "ENTRYPOINT STARTED"

cd /var/www

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
fi

if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

echo "Waiting for mysql..."

until php -r "
try {
    new PDO(
        'mysql:host=mysql;dbname=h5cf_db',
        'h5cf_user',
        'h5cf_pass',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo 'connected';
} catch (Throwable \$e) {
    fwrite(STDERR, \$e->getMessage());
    exit(1);
}
"; do
    sleep 2
done

echo "MySQL ready"

php artisan migrate --force

exec php-fpm
