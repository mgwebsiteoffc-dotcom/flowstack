<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand configuration
    |--------------------------------------------------------------------------
    | Single source of truth for the public website's accent color, hero copy
    | and real dashboard screenshots. Change these values (or the BRAND_* env
    | vars) and every marketing page updates - no markup edits needed.
    |
    | Screenshots: drop files into public/screenshots/ and set the names here
    | (or BRAND_DASHBOARD_SCREENSHOT=...). When the file exists it is rendered
    | in place of the CSS mockup; otherwise the mockup is shown.
    */

    // Accent color (hex). The indigo/purple Tailwind palettes are re-derived
    // from this in the <head> of every public page.
    'accent' => env('BRAND_COLOR', '#4f46e5'),

    // Secondary accent (gradients) - derived from accent when empty.
    'accent_secondary' => env('BRAND_COLOR_SECONDARY', ''),

    // Hero copy
    'hero_headline_1' => env('BRAND_HERO_1', 'Agency Management'),
    'hero_headline_2' => env('BRAND_HERO_2', 'for Modern Agencies'),
    'hero_sub' => env('BRAND_HERO_SUB', 'Replace guesswork and scattered spreadsheets with real-time agency intelligence. One platform for Clients, Projects, Tasks, Leads, Invoices and Reporting. Built for agencies of any size.'),

    // Real screenshots (relative to public/). Leave null/empty to use CSS mockups.
    'screenshot_dashboard' => env('BRAND_DASHBOARD_SCREENSHOT'),
    'screenshot_clients' => env('BRAND_SCREENSHOT_CLIENTS'),
    'screenshot_tasks' => env('BRAND_SCREENSHOT_TASKS'),
    'screenshot_leads' => env('BRAND_SCREENSHOT_LEADS'),
    'screenshot_finance' => env('BRAND_SCREENSHOT_FINANCE'),
];
