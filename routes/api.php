<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CaseApiController;
use App\Http\Controllers\EnumApiController;
use App\Http\Controllers\ItemApiController;
use App\Http\Controllers\MarketApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
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
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});

Route::get('/market', [MarketApiController::class, 'all']);
Route::post('/echo', function (Request $request) {
    return $request;
});

Route::prefix('clients')->group(function ()
{
    Route::get('', [ClientsController::class, 'all']);
    Route::prefix('/{identifier}')->group(function ()
    {
        Route::get('', [ClientsController::class, 'client']);
        Route::get('/inventory', [ClientsController::class, 'inventory']);
    });
});

Route::get('/cases', [CaseApiController::class, "cases"])->name("all_cases");
Route::get('/cases/{case_id}', [CaseApiController::class, "index"]);


Route::middleware('auth:sanctum')->group(function ()
{
    Route::post('/auth/deleteprofile', [AuthenticationController::class, 'DeleteProfile']);

    Route::prefix('cases')->group(function () {
        Route::post('', [CaseApiController::class, 'create'])->middleware("moderator");
        Route::post('/buy', [CaseApiController::class, 'buy']);
    });

    Route::get('/transaction_types', [EnumApiController::class, "transaction_type"]);
    Route::get('/permissions', [EnumApiController::class, "permissions"]);
    Route::get('/qualities', [EnumApiController::class, "qualities"]);

    Route::prefix('items')->group(function () {
        Route::get('', [ItemApiController::class, "items"]);
        Route::post('', [ItemApiController::class, "create"])->middleware("moderator");
        Route::get('/{item_id}', [ItemApiController::class, "index"]);
        Route::post('/sell', [ItemApiController::class, "sell"]);
    });

    Route::get('/transactions', [TransactionApiController::class, "transactions"]);
    Route::get('/transactions/{transaction_id}', [TransactionApiController::class, "index"]);

    Route::prefix('profile')->group(function ()
    {
        Route::get('', [ProfileController::class, 'profile']);
        Route::post('/accrue', [ProfileController::class, 'accrue']);
    });

    Route::post('/market/createlot', [MarketApiController::class, 'putItem']);
    Route::post('/market/buy', [MarketApiController::class, 'buyItem']);
});
