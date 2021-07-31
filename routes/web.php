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

    Route::get('admin', '\App\Http\Controllers\UserController@dashboard')->name("admin");
    Route::get('admin/users', '\App\Http\Controllers\AdminController@users')->name("admin/users");
    Route::get('admin/view-customer/{id}', '\App\Http\Controllers\AdminController@viewCustomer')->name("admin/view-customer");
    Route::get('admin/delete-customer/{id}', '\App\Http\Controllers\AdminController@deleteCustomer')->name("admin/delete-customer");
    Route::get('admin/view-advisor/{id}', '\App\Http\Controllers\AdminController@viewAdvisor')->name("admin/view-advisor");
    Route::get('admin/view-need/{id}', '\App\Http\Controllers\AdminController@viewNeeds')->name("admin/view-need");
    Route::get('admin/advisors', '\App\Http\Controllers\AdminController@Advisors')->name("admin/advisors");
    Route::get('admin/delete-advisor/{id}', '\App\Http\Controllers\AdminController@deleteAdvisor')->name("admin/delete-advisor");
    Route::get('admin/needList', '\App\Http\Controllers\AdminController@needList')->name("admin/needList");
    Route::get('admin/delete-need/{id}', '\App\Http\Controllers\AdminController@deleteNeed')->name("admin/delete-need");
    Route::get('/', '\App\Http\Controllers\UserController@index');
    Route::get('home', '\App\Http\Controllers\UserController@index')->name("home");
    Route::get('profile', '\App\Http\Controllers\UserController@index')->name("profile.edit");
    Route::get('user', '\App\Http\Controllers\UserController@index')->name("user.index");
    Route::get('icons', '\App\Http\Controllers\UserController@index')->name("icons");
    Route::get('map', '\App\Http\Controllers\UserController@index')->name("map");
    Route::get('table', '\App\Http\Controllers\UserController@index')->name("table");
    Route::get('update', '\App\Http\Controllers\UserController@index')->name("profile.update");
    Route::get('password', '\App\Http\Controllers\UserController@index')->name("profile.password");
    //company
    Route::get('admin/companies', '\App\Http\Controllers\AdminController@companyList')->name("admin/companies");
    Route::get('admin/view-company/{id}', '\App\Http\Controllers\AdminController@viewCompany')->name("admin/view-company");
    Route::get('admin/delete-company/{id}', '\App\Http\Controllers\AdminController@deleteCompany')->name("admin/delete-company");
    Route::get('admin/pages', '\App\Http\Controllers\AdminController@pages')->name("admin/pages");
    Route::get('admin/addPage', '\App\Http\Controllers\AdminController@addPage')->name("admin/addPage");
    Route::post('admin/savePage', '\App\Http\Controllers\AdminController@savePage')->name("admin/savePage");
    Route::get('admin/edit-page/{id}', '\App\Http\Controllers\AdminController@editPage')->name("admin/edit-page");
    Route::get('admin/delete-page/{id}', '\App\Http\Controllers\AdminController@deletePage')->name("admin/delete-page");
    Route::post('admin/updatePage', '\App\Http\Controllers\AdminController@updatePage')->name("admin/updatePage");
    Route::get('admin/status-page/{status}/{id}', '\App\Http\Controllers\AdminController@pageStatus')->name("admin/status-page");
    // Services
    Route::get('admin/services', '\App\Http\Controllers\AdminController@services')->name("admin/services");
    Route::get('admin/addService', '\App\Http\Controllers\AdminController@addService')->name("admin/addService");
    Route::post('admin/saveService', '\App\Http\Controllers\AdminController@saveService')->name("admin/saveService");
    Route::get('admin/edit-service/{id}', '\App\Http\Controllers\AdminController@editService')->name("admin/edit-service");
    Route::get('admin/delete-service/{id}', '\App\Http\Controllers\AdminController@deleteService')->name("admin/delete-service");
    Route::post('admin/updateService', '\App\Http\Controllers\AdminController@updateService')->name("admin/updateService");
    Route::get('admin/status-service/{status}/{id}', '\App\Http\Controllers\AdminController@serviceStatus')->name("admin/status-service");
    
});