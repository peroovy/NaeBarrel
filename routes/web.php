<?php

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

Route::get('/api/cases', [\App\Http\Controllers\CaseApiController::class, "cases"]);

Route::get('/api/cases/{case}', [\App\Http\Controllers\CaseApiController::class, "index"]);

Route::get('/api/cases/{case}/items', [\App\Http\Controllers\CaseApiController::class, "items"]);
