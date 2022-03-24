<?php

use App\Http\Controllers\CaseApiController;
use App\Http\Controllers\EnumApiController;
use App\Http\Controllers\ItemApiController;
use App\Http\Controllers\TransactionApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/cases', [CaseApiController::class, "cases"]);
Route::get('/api/cases/{case}', [CaseApiController::class, "index"]);
Route::get('/api/cases/{case}/items', [CaseApiController::class, "items"]);

Route::get('/api/transaction_types', [EnumApiController::class, "transaction_type"]);
Route::get('/api/permissions', [EnumApiController::class, "permissions"]);
Route::get('/api/qualities', [EnumApiController::class, "qualities"]);

Route::get('/api/items', [ItemApiController::class, "items"]);
Route::get('/api/items/{item_id}', [ItemApiController::class, "index"]);
Route::get('/api/items/quality/{quality_id}', [ItemApiController::class, "quality"]);

Route::get('/api/transactions', [TransactionApiController::class, "transactions"]);
Route::get('/api/transactions/{transaction_id}', [TransactionApiController::class, "index"]);
Route::get('/api/transactions/type/{type_id}', [TransactionApiController::class, "type"]);
