<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Home');
});

Route::get('/about-us', function () {
    return inertia('AboutUs');
});

Route::get('/contact-us', function () {
    return inertia('ContactUs');
});
