<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\CarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TransactionController;

use App\Http\Middleware\OptionalAuthSanctum;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::get('/cars/makes', function ()
    {
        return DB::table('cars')->select('manufacturer')->distinct()->get()->pluck('manufacturer');
    }
);
Route::get('/cars/models', function ()
    {
        return DB::table('cars')->select('model')->distinct()->get()->pluck('model');
    }
);
Route::get('/cars/bodies', function ()
    {
        return DB::table('cars')->select('body')->distinct()->get()->pluck('body');
    }
);
Route::get('/cars/colors', function ()
    {
        return DB::table('cars')->select('color')->distinct()->get()->pluck('color');
    }
);
Route::get('/cars/{car_id}', [CarController::class, 'show'])->middleware([OptionalAuthSanctum::class]);
Route::get('/cars', [CarController::class, 'search'])->middleware([OptionalAuthSanctum::class]);

Route::post('/users', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes
Route::group(
    ['middleware' => ['auth:sanctum']], function ()
    {
        Route::post('/logout', [UserController::class, 'logout']);

        Route::get('/reservations', [ReservationController::class, 'getReservation']);
        Route::post('/reservations', [ReservationController::class, 'createReservation']);

        Route::get('/transactions/{transaction_id}', [TransactionController::class, 'getTransactionById']);
        Route::get('/transactions', [TransactionController::class,'getTransactions']);
        Route::post('/transactions', [TransactionController::class, 'createTransaction']);
    }
);
