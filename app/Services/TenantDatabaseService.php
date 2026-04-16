<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantDatabaseService
{
    public static function schemaName(Tenant|int $tenant): string
    {
        $id = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return 'tenant_' . $id;
    }

    public static function createSchema(Tenant $tenant): void
    {
        $schema = self::schemaName($tenant);

        DB::statement('CREATE SCHEMA IF NOT EXISTS ' . self::quoteIdentifier($schema));
    }

    public static function dropSchema(Tenant $tenant): void
    {
        $schema = self::schemaName($tenant);

        DB::statement('DROP SCHEMA IF EXISTS ' . self::quoteIdentifier($schema) . ' CASCADE');
    }

    public static function switchTo(Tenant|int $tenant): void
    {
        $schema = self::schemaName($tenant);

        $config = config('database.connections.tenant');
        $config['search_path'] = $schema . ',public';

        config(['database.connections.tenant' => $config]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public static function reset(): void
    {
        $config = config('database.connections.tenant');
        $config['search_path'] = 'public';

        config(['database.connections.tenant' => $config]);

        DB::purge('tenant');
    }

    public static function migrateSchema(Tenant $tenant): void
    {
        self::switchTo($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }

    private static function quoteIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
