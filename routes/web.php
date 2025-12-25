<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\Pages\AdvertiseShowController;
use App\Http\Controllers\Pages\MainPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', MainPageController::class)->name('home');
Route::get('/advertise', AdvertiseShowController::class)->name('advertise');
Route::post('/callback', [CallbackController::class, 'sendCallback'])->name('callback.send');
