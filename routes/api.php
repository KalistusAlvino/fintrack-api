<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(RegisterController::class)->group( function () {
    // --------------- Register and Login ----------------//
    Route::post('register', 'register')->name('register');
    Route::get('verify/{token}', 'verify')->name('verify');
    Route::post('resend-verification', 'resendVerification')->name('resend-verification');
});
