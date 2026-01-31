<?php

return [
    /*
     * Multi-tenancy scaffold (disabled by default).
     *
     * This project ships single-tenant by default, but includes a tenant context
     * so future apps can become multi-tenant without a large refactor.
     */
    'enabled' => (bool) env('TENANCY_ENABLED', false),

    /*
     * Resolution strategy:
     * - subdomain: {tenant}.yourdomain.com
     * - header:    X-Tenant: tenant_key
     */
    'strategy' => env('TENANCY_STRATEGY', 'subdomain'),

    // Only used when strategy=header
    'header_name' => env('TENANCY_HEADER', 'X-Tenant'),

    // Reserved subdomains that should NOT be treated as a tenant key.
    'reserved_subdomains' => ['www', 'api', 'admin', 'bsadm', 'localhost'],
];


