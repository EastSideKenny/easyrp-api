<?php

use App\Providers\AppServiceProvider;

/**
 * Dev-only providers are not auto-discovered (see composer.json dont-discover)
 * so Docker / `composer install --no-dev` never loads missing classes from a
 * stale bootstrap/cache/packages.php generated on a full dev install.
 */
return array_values(array_filter([
    AppServiceProvider::class,
    class_exists(\Laravel\Pail\PailServiceProvider::class)
        ? \Laravel\Pail\PailServiceProvider::class
        : null,
    class_exists(\NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider::class)
        ? \NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider::class
        : null,
]));
