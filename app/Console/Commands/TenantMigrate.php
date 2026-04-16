<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantDatabaseService;
use Illuminate\Console\Command;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate {--tenant= : Migrate a specific tenant by ID} {--fresh : Drop and re-create all tenant schemas}';

    protected $description = 'Run tenant-scoped migrations for all or a specific tenant';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $fresh = $this->option('fresh');

        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found.');
            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $schema = TenantDatabaseService::schemaName($tenant);

            $this->info("Migrating tenant #{$tenant->id} ({$tenant->name}) → schema: {$schema}");

            if ($fresh) {
                TenantDatabaseService::dropSchema($tenant);
            }

            TenantDatabaseService::createSchema($tenant);
            TenantDatabaseService::migrateSchema($tenant);

            $this->info("  ✓ Done");
        }

        TenantDatabaseService::reset();

        $this->info('All tenant migrations completed.');

        return self::SUCCESS;
    }
}
