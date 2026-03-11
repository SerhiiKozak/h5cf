#!/bin/sh

set -e

cd /var/www

# install dependencies if vendor missing
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist
fi

# generate app key if not exists
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate
fi

# wait for mysql
echo "Waiting for mysql..."

until php -r "
try {
    new PDO('mysql:host=mysql;dbname=h5cf_db','h5cf_user','h5cf_pass');
    echo 'connected';
} catch (Exception \$e) {
    exit(1);
}
"; do
  sleep 2
done

echo "MySQL ready"

php artisan migrate --force

exec php-fpm
