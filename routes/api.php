<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\User\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(RegisterController::class)->group(function () {
    // --------------- Register and Login ----------------//
    Route::post('register', 'register')->name('register');
    Route::get('verify/{token}', 'verify')->name('verify');

    Route::post('resend-verification', 'resendVerification')->name('resend-verification');
});

Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login')->name('login');
});

Route::middleware('auth:api')->group(function () {
    Route::controller(WalletController::class)->group(function () {
        Route::get('wallet', 'index')->name('wallet.index');
        Route::get('wallet/income', 'income')->name('wallet.income');
        Route::get('wallet/monthly-income', 'monthlyIncome')->name('wallet.monthly-income');
        Route::get('wallet/expenses', 'expenses')->name('wallet.expenses');
        Route::get('wallet/this-month-income', 'thisMonthIncome')->name('wallet.thisMonthIncome');

        Route::post('wallet/income-category', 'incomeCategoryPost')->name('wallet.income-category.post');
        Route::post('wallet/income', 'incomePost')->name('wallet.income-category.post');
    });
});
