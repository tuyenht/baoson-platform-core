<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$adminPrefix = config('admin.prefix', 'bsadm');

Route::get('/', function () use ($adminPrefix) {
    return redirect("/{$adminPrefix}");
});

Route::prefix($adminPrefix)->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('admin.dashboard');
});
