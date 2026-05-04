#!/bin/sh
set -e
cd /var/www
# Rebuild package manifest to match installed Composer packages (e.g. --no-dev in Docker
# vs dev packages locally). Prevents "Class ... PailServiceProvider not found" when host
# cache was generated with require-dev packages.
if [ -f artisan ]; then
    php artisan package:discover --ansi 2>/dev/null || true
fi
exec "$@"
