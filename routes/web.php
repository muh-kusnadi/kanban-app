<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

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

Route::get('/', [TaskController::class, 'home'])
    ->name('home')
    ->middleware('auth');

Route::name('auth.')
    ->controller(AuthController::class)
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('signup', 'signupForm')->name('signupForm');
            Route::post('signup', 'signup')->name('signup');
            Route::get('login', 'loginForm')->name('loginForm');
            Route::post('login', 'login')->name('login');
        });

        Route::middleware('auth')->group(function () {
            Route::post('logout', 'logout')->name('logout');
        });
    });

Route::prefix('tasks')
    ->name('tasks.')
    ->middleware('auth')
    ->controller(TaskController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('create/{status?}', 'create')->name('create');
        Route::get('progress', 'progress')->name('progress');

        Route::put('{id}/update', 'update')->name('update');
        Route::get('{id}/delete', 'delete')->name('delete');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::patch('{id}/move', 'move')->name('move');
        Route::delete('{id}/destroy', 'destroy')->name('destroy');

    });