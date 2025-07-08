<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('welcome'); // this will load resources/views/app.blade.php
})->where('any', '.*');
