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
Route::get('clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
});
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
    /******************* Admin Profile Management **********************/
    Route::get('admin/profile', '\App\Http\Controllers\ProfileController@edit');
    Route::post('admin/updateProfile','\App\Http\Controllers\ProfileController@update');
    /******************* Admin Profile Management **********************/

    /******************* User Management **********************/
    Route::get('admin/users', '\App\Http\Controllers\UserController@index')->name("admin/users");
    Route::get('admin/users/show/{id}', '\App\Http\Controllers\UserController@show')->name("admin/users/show");
    Route::get('admin/delete-customer/{id}', '\App\Http\Controllers\UserController@destroy')->name("admin/delete-customer");
    Route::post('admin/updatePassword','\App\Http\Controllers\UserController@updatePassword');
    Route::post('admin/deleteCustomer','\App\Http\Controllers\UserController@deleteCustomer');
    Route::post('admin/update-user-status','\App\Http\Controllers\UserController@updateStatus');

    /******************* User Management **********************/

    /******************* Advisor Management **********************/
    Route::get('admin/advisors', '\App\Http\Controllers\AdvisorController@index')->name("admin/advisors");
    Route::get('admin/advisors/show/{id}', '\App\Http\Controllers\AdvisorController@show')->name("admin/advisors/show");
    Route::get('admin/delete-advisor/{id}', '\App\Http\Controllers\AdvisorController@destroy')->name("admin/delete-advisor");
    Route::post('admin/update-fca-verification-status','\App\Http\Controllers\AdvisorController@updateFCAStatus');
    Route::post('admin/update-advisor-status','\App\Http\Controllers\AdvisorController@updateAdvisorStatus');
    Route::post('admin/reset-password','\App\Http\Controllers\AdvisorController@resetPassword');
    Route::get('admin/advisors/invoice/{id}', '\App\Http\Controllers\AdvisorController@invoice')->name("admin/advisors/invoice");
    /******************* Advisor Management **********************/

    /******************* Need Management **********************/
    Route::get('admin/need', '\App\Http\Controllers\NeedController@index')->name("admin/need");
    Route::get('admin/need/show/{id}', '\App\Http\Controllers\NeedController@show')->name("admin/need/show");
    Route::get('admin/delete-need/{id}', '\App\Http\Controllers\NeedController@destroy')->name("admin/delete-need");
    Route::post('admin/update-need-status','\App\Http\Controllers\NeedController@updateNeedStatus');

    /******************* Need Management **********************/

    /******************* Company Management **********************/
    Route::get('admin/companies', '\App\Http\Controllers\CompanyController@index')->name("admin/companies");
    Route::get('admin/company/show/{id}', '\App\Http\Controllers\CompanyController@show')->name("admin/company/show");
    Route::get('admin/delete-company/{id}', '\App\Http\Controllers\CompanyController@destroy')->name("admin/delete-company");
    Route::post('admin/update-company-status','\App\Http\Controllers\CompanyController@updateCompanyStatus');
    Route::post('admin/add-notes','\App\Http\Controllers\CompanyController@addNotes');    
    /******************* Company Management **********************/

    /******************* Pages Management **********************/
    Route::get('admin/pages', '\App\Http\Controllers\PagesController@index')->name("admin/pages");
    Route::get('admin/delete-page/{id}', '\App\Http\Controllers\PagesController@destroy')->name("admin/delete-page");
    Route::get('admin/pages/edit/{id}', '\App\Http\Controllers\PagesController@edit')->name("admin/pages/edit");
    Route::post('admin/update-page','\App\Http\Controllers\PagesController@update');
    Route::post('admin/update-page-status','\App\Http\Controllers\PagesController@updateStatus');
    /******************* Pages Management **********************/

    /******************* Services Management **********************/
    Route::get('admin/services', '\App\Http\Controllers\ServicesController@index')->name("admin/services");
    Route::get('admin/delete-service/{id}', '\App\Http\Controllers\ServicesController@destroy')->name("admin/delete-service");
    Route::post('admin/add-update-service','\App\Http\Controllers\ServicesController@store');
    Route::post('admin/get-service','\App\Http\Controllers\ServicesController@show');
    Route::post('admin/updateSequence','\App\Http\Controllers\ServicesController@updateSequence');
    Route::post('admin/update-service-status','\App\Http\Controllers\ServicesController@updateServiceStatus');
    /******************* Services Management **********************/

    /******************* Faq Category Management **********************/
    Route::get('admin/faq-category', '\App\Http\Controllers\FaqCategoryController@index')->name("admin/faq-category");
    Route::get('admin/delete-faq-categories/{id}', '\App\Http\Controllers\FaqCategoryController@destroy')->name("admin/delete-faq-categories");
    Route::post('admin/add-update-faq-category','\App\Http\Controllers\FaqCategoryController@store');
    Route::post('admin/get-faq-category','\App\Http\Controllers\FaqCategoryController@show');
    Route::post('admin/update-faq-category-status','\App\Http\Controllers\FaqCategoryController@updateFaqCategoryStatus');
    /******************* Faq Category Management **********************/

    /******************* Faq Management **********************/
    Route::get('admin/faq', '\App\Http\Controllers\FaqController@index')->name("admin/faq");
    Route::get('admin/delete-faq/{id}', '\App\Http\Controllers\FaqController@destroy')->name("admin/delete-faq");
    Route::get('admin/faq/create', '\App\Http\Controllers\FaqController@create')->name("admin/faq/create");
    Route::get('admin/faq/edit/{id}', '\App\Http\Controllers\FaqController@edit')->name("admin/faq/edit");
    Route::post('admin/update-faq','\App\Http\Controllers\FaqController@update');
    Route::post('admin/add-update-faq','\App\Http\Controllers\FaqController@store');
    Route::post('admin/get-audience','\App\Http\Controllers\FaqController@getAudience');
    Route::post('admin/update-faq-status','\App\Http\Controllers\FaqController@updateFaqStatus');
    /******************* Faq Management **********************/

    /******************* Faq Management **********************/
    Route::get('admin/setting/{type}', '\App\Http\Controllers\AppSettingsController@edit');
    Route::post('admin/update-setting','\App\Http\Controllers\AppSettingsController@updateSetting');
    /******************* Faq Management **********************/

    /******************* Invoice Management **********************/
    Route::get('admin/invoice', '\App\Http\Controllers\InvoiceController@index');
    Route::get('admin/invoice-list/{month}', '\App\Http\Controllers\InvoiceController@list');
    Route::get('admin/invoice-detail/{invoice_id}', '\App\Http\Controllers\InvoiceController@show');
    /******************* Invoice Management **********************/

    Route::get('admin', '\App\Http\Controllers\AdminController@index')->name("admin");
    Route::get('/', '\App\Http\Controllers\AdminController@index');
    Route::get('home', '\App\Http\Controllers\UserController@index')->name("home");
    Route::get('profile', '\App\Http\Controllers\UserController@index')->name("profile.edit");
    Route::get('user', '\App\Http\Controllers\UserController@index')->name("user.index");
    Route::get('icons', '\App\Http\Controllers\UserController@index')->name("icons");
    Route::get('map', '\App\Http\Controllers\UserController@index')->name("map");
    Route::get('table', '\App\Http\Controllers\UserController@index')->name("table");
    Route::get('update', '\App\Http\Controllers\UserController@index')->name("profile.update");
    Route::get('password', '\App\Http\Controllers\UserController@index')->name("profile.password");
});