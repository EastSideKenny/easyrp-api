<?php

namespace App\Mail\Concerns;

use App\Models\Tenant;
use App\Services\TenantDatabaseService;

trait SetsTenantSchema
{
    public int $tenantId;
    public string $tenantName;

    public function initializeSetsTenantSchema(): void
    {
        $this->tenantId = auth()->user()?->tenant_id
            ?? $this->tenantId
            ?? 0;

        $tenant = $this->tenantId ? Tenant::find($this->tenantId) : null;
        $this->tenantName = $tenant?->name ?? config('app.name');
    }

    /**
     * Override __unserialize to set up the tenant schema before
     * SerializesModels restores Eloquent models from the queue.
     */
    public function __unserialize(array $values): void
    {
        if (isset($values['tenantId']) && $values['tenantId'] > 0) {
            TenantDatabaseService::switchTo($values['tenantId']);
        }

        // Delegate to SerializesModels::__unserialize
        $this->unserializeFromSerializesModels($values);
    }
}
