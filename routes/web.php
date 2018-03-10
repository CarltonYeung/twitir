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
Route::get('/', 'HomepageController@index');

// adduser
Route::get('/adduser', 'UsersController@index')->name('adduser');
Route::post('/adduser', 'UsersController@adduser');

// verify
Route::get('/verify', 'EmailVerificationController@index')->name('verify');

// Placeholder routes
Route::get('/login', 'HomepageController@index')->name('login');
