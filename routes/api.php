<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\User\WalletController;
use App\Http\Controllers\Wallet\ExpensesController;
use App\Http\Controllers\Wallet\IncomeController;
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
        Route::get('profile', 'profile')->name('profile');
        Route::post('logout','logout')->name('logout');
    });

    Route::controller(IncomeController::class)->group(function () {
        //Category
        //Get
        Route::get('wallet/income-category', 'getIncomeCategory')->name('wallet.income-category');
        //Post
        Route::post('wallet/income-category', 'incomeCategoryPost')->name('wallet.income-category.post');
        //Income
        //Get
        Route::get('wallet/income', 'income')->name('wallet.income');
        Route::get('wallet/monthly-income', 'monthlyIncome')->name('wallet.monthly-income');
        Route::get('wallet/this-month-income', 'thisMonthIncome')->name('wallet.thisMonthIncome');
        Route::get('wallet/all-income', 'allIncome')->name('wallet.all-income');
        //Post
        Route::post('wallet/income', 'incomePost')->name('wallet.income.post');
    });

    Route::controller(ExpensesController::class)->group(function () {
        //Category
        //Get
        Route::get('wallet/expenses-category', 'getExpensesCategory')->name('wallet.expenses-category');
        //Post
        Route::post('wallet/expenses-category', 'expensesCategoryPost')->name('wallet.expenses-category.post');
        //Expenses
        //Get
        Route::get('wallet/expenses', 'expenses')->name('wallet.expenses');
        Route::get('wallet/monthly-expenses', 'monthlyExpenses')->name('wallet.monthly-expenses');
        Route::get('wallet/this-month-expenses', 'thisMonthExpenses')->name('wallet.thisMonthExpenses');
        Route::get('wallet/all-expenses', 'allExpenses')->name('wallet.all-expenses');
        //Post
        Route::post('wallet/expenses', 'expensesPost')->name('wallet.expenses.post');
    });
});
