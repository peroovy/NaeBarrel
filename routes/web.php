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
    return view('index');
});

Route::get('/open', function () {
    return view('roulette');
});

Route::get('/login', function () {
    return view('auth');
});

Route::get('/logout', function () {
    return view('logout');
});

Route::get('/deleteprofile', function () {
    return view('delete');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/profile/{login}', function ($login=false) {
    return view('inventory', ["login" => $login]);
});
