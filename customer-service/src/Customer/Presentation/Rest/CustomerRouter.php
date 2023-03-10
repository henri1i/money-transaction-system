<?php

namespace Src\Customer\Presentation\Rest;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Src\Customer\Presentation\Rest\Controllers\AuthController;
use Src\Customer\Presentation\Rest\Controllers\WalletController;

class CustomerRouter extends RouteServiceProvider
{
    public function register()
    {
        Route::prefix('customer')->name('customer.')->middleware('api')->group(function () {
            Route::prefix('auth')->name('auth.')->group(function () {
                Route::post('/register', [AuthController::class, 'register'])->name('register');
                Route::post('/login', [AuthController::class, 'login'])->name('login');
            });

            Route::prefix('wallet')->name('wallet.')->middleware('auth')->group(function () {
                Route::get('/balance', [WalletController::class, 'getBalance'])->name('balance');
                Route::get('/transaction', [WalletController::class, 'getTransactions'])->name('get-transactions');
                Route::post('/transaction', [WalletController::class, 'sendTransaction'])->name('send-transaction');
            });
        });
    }
}
