<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Schema Package Routes
|--------------------------------------------------------------------------
|
| Here is where you can register routes for your package.
|
*/

Route::middleware(['web', 'auth'])->prefix('schema')->name('schema.')->group(function () {
    // Schema package routes could be added here
    // For example, a web interface to manage schemas
});

// Add any custom routes here
// These routes will be loaded by the SchemaServiceProvider

// Route::middleware(['web', 'auth'])->prefix('schema-generator')->group(function () {
//     Route::get('/', [SchemaController::class, 'index'])->name('schema.index');
// });
