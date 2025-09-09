<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/mail', function() {
    return view('mail');
});

Route::get('/success-verify', function() {
    return view('success-verify');
});
Route::get('/failed-verify', function() {
    return view('failed-verify');
});


