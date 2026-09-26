<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Frontend fixtures are never registered outside local development or tests.
if (app()->environment(['local', 'testing'])) {
    require __DIR__.'/preview.php';
}
