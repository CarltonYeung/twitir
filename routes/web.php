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

// Homepage
Route::get('/', 'HomepageController@index')->name('home');

// adduser
Route::get('/adduser', 'UsersController@index')->name('adduser');
Route::post('/adduser', 'UsersController@adduser');

// verify
Route::get('/verify', 'EmailVerificationController@index')->name('verify');
Route::post('/verify', 'EmailVerificationController@verify');

// login
Route::get('/login', 'LoginController@index')->name('login');
Route::post('/login', 'LoginController@login');

// logout
Route::post('/logout', 'LogoutController@logout')->name('logout');
Route::get('/logout', 'LogoutController@logout');

// tweets/items/posts
Route::get('/additem', 'ItemsController@index')->name('additem');
Route::post('/additem', 'ItemsController@additem');
Route::get('item/{id}', 'ItemsController@getitem')->name('getitem');
