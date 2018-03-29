<?php

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
Route::get('/test', 'TestController@test');
Route::get('/api/test', 'StockController@test');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'api/v1', 'middleware' => 'auth'], function () {
    Route::get('/quotes', 'QuoteController@index');
    Route::get('/stocks', 'StockController@index');
    Route::get('/users/{id}/quotes', 'UserController@getUserStockQuotes');
    Route::post('/users/{id}/stocks', 'UserController@addStock');
    Route::get('/users/{id}/stocks-quotes', 'UserController@getStockQuotes');
    Route::delete('/users/{id}/stocks/{stockId}', 'UserController@deleteStock');
    Route::get('/users/info', 'UserController@getCurrentUserInfo');
    Route::post('/users/{id}/warning-configs', 'UserController@addWarningConfig');
    Route::get('/users/{id}/warning-configs', 'UserController@getWarningConfigs');
    Route::get('/notification-types', 'NotificationTypeController@index');
});

