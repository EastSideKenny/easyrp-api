#!/bin/sh
set -e
cd /var/www

# Ensure Laravel runtime paths are always writable, even with bind-mounted code.
mkdir -p storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# Rebuild package manifest to match installed Composer packages (e.g. --no-dev in Docker
# vs dev packages locally). Prevents "Class ... PailServiceProvider not found" when host
# cache was generated with require-dev packages.
if [ -f artisan ]; then
    php artisan package:discover --ansi 2>/dev/null || true
fi
exec "$@"
