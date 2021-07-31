<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\LoginController;


Route::middleware(['admin', 'auth'])->group(function () {

    Route::prefix('admin')->group(function () {

// Route::post('login/', 'LoginController@redirectToProvider');
// Route::get('login/{provider}/callback', 'Auth\LoginController@handleProviderCallback');
        Route::get('login', function () {
            return view("auth.login");
        });
        Route::get("/{any}",'AdminController@index');
    });
});

