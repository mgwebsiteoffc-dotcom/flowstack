<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant base domain
    |--------------------------------------------------------------------------
    | Agencies (tenants) are addressed as {slug}.{tenant_domain}. The bare
    | {tenant_domain} host shows the public marketing site / registration page.
    */
    'tenant_domain' => env('TENANT_DOMAIN', 'yoursaas.com'),

    /*
    | During local development (artisan serve) there is no wildcard DNS, so when
    | the request host carries no subdomain we fall back to the first active
    | tenant. Keep this disabled in production.
    */
    'fallback_to_single_tenant' => env('TENANT_FALLBACK_SINGLE', true),

    /*
    | Grace period (days) after a trial expires before tenant data is deleted.
    */
    'trial_grace_days' => env('TRIAL_GRACE_DAYS', 30),

    /*
    | How long webhook logs are kept (days).
    */
    'webhook_log_retention_days' => env('WEBHOOK_LOG_RETENTION_DAYS', 90),

    /*
    | Pagination size for tenant-scoped lists.
    */
    'pagination_size' => 15,

];
