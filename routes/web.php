<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\UserController;
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
Route::get('lang/{locale}',function($lang){
    \Session::put('locale',$lang);
    return redirect()->back();   
})->name("locale");

 
Auth::routes(['verify' => true]);
Route::get("/account",function ()
{
    return "hello ".auth()->user()->fullname;
})->middleware("verified")->name("account");
Route::get('login/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('login/{provider}/callback', 'Auth\LoginController@handleProviderCallback');

Route::get("/verify-success",function(){
    return "your account has been verified!";
});

Route::middleware('auth')->group(function(){

    /******************* User Management **********************/
    Route::get('admin/users', '\App\Http\Controllers\UserController@index')->name("admin/users");
    Route::get('admin/users/show/{id}', '\App\Http\Controllers\UserController@show')->name("admin/users/show");
    Route::get('admin/delete-customer/{id}', '\App\Http\Controllers\UserController@destroy')->name("admin/delete-customer");
    /******************* User Management **********************/

    /******************* Advisor Management **********************/
    Route::get('admin/advisors', '\App\Http\Controllers\AdvisorController@index')->name("admin/advisors");
    Route::get('admin/advisors/show/{id}', '\App\Http\Controllers\AdvisorController@show')->name("admin/advisors/show");
    Route::get('admin/delete-advisor/{id}', '\App\Http\Controllers\AdvisorController@destroy')->name("admin/delete-advisor");
    /******************* Advisor Management **********************/

    /******************* Need Management **********************/
    Route::get('admin/need', '\App\Http\Controllers\NeedController@index')->name("admin/need");
    Route::get('admin/need/show/{id}', '\App\Http\Controllers\NeedController@show')->name("admin/need/show");
    Route::get('admin/delete-need/{id}', '\App\Http\Controllers\NeedController@destroy')->name("admin/delete-need");
    /******************* Need Management **********************/

    /******************* Company Management **********************/
    Route::get('admin/companies', '\App\Http\Controllers\CompanyController@index')->name("admin/companies");
    // Route::get('admin/company/show/{id}', '\App\Http\Controllers\CompanyController@show')->name("admin/company/show");
    Route::get('admin/delete-company/{id}', '\App\Http\Controllers\CompanyController@destroy')->name("admin/delete-company");
    /******************* Company Management **********************/

    /******************* Pages Management **********************/
    Route::get('admin/pages', '\App\Http\Controllers\PagesController@index')->name("admin/pages");
    Route::get('admin/delete-page/{id}', '\App\Http\Controllers\PagesController@destroy')->name("admin/delete-page");
    /******************* Pages Management **********************/

    /******************* Services Management **********************/
    Route::get('admin/services', '\App\Http\Controllers\ServicesController@index')->name("admin/services");
    Route::get('admin/delete-service/{id}', '\App\Http\Controllers\ServicesController@destroy')->name("admin/delete-service");
    /******************* Services Management **********************/

    Route::get('admin', '\App\Http\Controllers\UserController@dashboard')->name("admin");
    Route::get('/', '\App\Http\Controllers\UserController@index');
    Route::get('home', '\App\Http\Controllers\UserController@index')->name("home");
    Route::get('profile', '\App\Http\Controllers\UserController@index')->name("profile.edit");
    Route::get('user', '\App\Http\Controllers\UserController@index')->name("user.index");
    Route::get('icons', '\App\Http\Controllers\UserController@index')->name("icons");
    Route::get('map', '\App\Http\Controllers\UserController@index')->name("map");
    Route::get('table', '\App\Http\Controllers\UserController@index')->name("table");
    Route::get('update', '\App\Http\Controllers\UserController@index')->name("profile.update");
    Route::get('password', '\App\Http\Controllers\UserController@index')->name("profile.password");
});