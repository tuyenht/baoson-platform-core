<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    /*
     * Explicitly register Ziggy to ensure the Blade `@routes` directive is available
     * even when package discovery / cached manifests are out of sync.
     */
    Tighten\Ziggy\ZiggyServiceProvider::class,
];
