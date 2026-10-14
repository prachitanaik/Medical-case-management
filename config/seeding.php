<?php

/*
 * Dummy login details used only by the database seeder.
 * The values live in .env (see .env.example and README.md), not in code.
 */
return [
    'admin_email' => env('SEED_ADMIN_EMAIL'),
    'admin_password' => env('SEED_ADMIN_PASSWORD'),
    'viewer_email' => env('SEED_VIEWER_EMAIL'),
    'viewer_password' => env('SEED_VIEWER_PASSWORD'),
];
