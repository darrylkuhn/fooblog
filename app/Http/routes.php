<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::resource('oauth', 'Auth\OAuthController');

Route::resource('blog', 'BlogController', ['except'=>'store']);

Route::group(['middleware' => 'oauth'], function()
{
    Route::resource('blog', 'BlogController', ['only'=>'store']);
    Route::resource('user', 'UserController');
});