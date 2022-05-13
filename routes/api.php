<?php

use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\CaseApiController;
use App\Http\Controllers\EnumApiController;
use App\Http\Controllers\ItemApiController;
use App\Http\Controllers\TransactionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\ClientsController;

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
Route::prefix('auth')->group(function ()
{
    Route::post('register', [AuthorizationController::class, 'register']);
    Route::post('login', [AuthorizationController::class, 'login']);
    Route::post('logout', [AuthorizationController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function ()
{
    Route::prefix('clients')->group(function ()
    {
        Route::get('/', [ClientsController::class, 'all']);
        Route::prefix('/{identifier}')->group(function ()
        {
            Route::get('/', [ClientsController::class, 'client']);
            Route::get('/inventory', [ClientsController::class, 'inventory']);
        });
    });

    Route::prefix('cases')->group(function () {
        Route::get('/', [CaseApiController::class, "cases"]);
        Route::post('/', [CaseApiController::class, 'create'])->middleware("moderator");
        Route::get('/{case_id}', [CaseApiController::class, "index"]);
        Route::post('/buy', [CaseApiController::class, 'buy']);
    });

    Route::get('/transaction_types', [EnumApiController::class, "transaction_type"]);
    Route::get('/permissions', [EnumApiController::class, "permissions"]);
    Route::get('/qualities', [EnumApiController::class, "qualities"]);

    Route::get('/items', [ItemApiController::class, "items"]);
    Route::get('/items/{item_id}', [ItemApiController::class, "index"]);

    Route::get('/transactions', [TransactionApiController::class, "transactions"]);
    Route::get('/transactions/{transaction_id}', [TransactionApiController::class, "index"]);

});
