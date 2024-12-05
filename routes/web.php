<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});


Route::get('/{any}', function () {
    return view('app'); // Replace 'app' with the name of your Blade view containing the React app
})->where('any', '.*');
