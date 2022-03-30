<?php

use App\Http\Controllers\CaseApiController;
use App\Http\Controllers\EnumApiController;
use App\Http\Controllers\ItemApiController;
use App\Http\Controllers\TransactionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/cases', [CaseApiController::class, "cases"]);
Route::get('/cases/{case_id}', [CaseApiController::class, "index"]);

Route::get('/transaction_types', [EnumApiController::class, "transaction_type"]);
Route::get('/permissions', [EnumApiController::class, "permissions"]);
Route::get('/qualities', [EnumApiController::class, "qualities"]);

Route::get('/items', [ItemApiController::class, "items"]);
Route::get('/items/{item_id}', [ItemApiController::class, "index"]);

Route::get('/transactions', [TransactionApiController::class, "transactions"]);
Route::get('/transactions/{transaction_id}', [TransactionApiController::class, "index"]);
