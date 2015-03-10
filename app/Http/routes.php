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

Route::get('/', 'Welcome@index');

Route::get('home', 'Home@index');

Route::controllers([
	'auth' => 'Auth\Auth',
	'password' => 'Auth\Password',
]);

Route::resource('users','Users',['except'=>'index','create','store','destroy']);
Route::resource('streamers','Streamers',['only'=>'index','show']);
Route::resource('pledges','Pledges',['except'=>'destroy']);
Route::resource('users.pledges','UsersPledges', ['only'=>'index']);
Route::resource('streamers.pledges','StreamersPledges', ['only'=>'index']);