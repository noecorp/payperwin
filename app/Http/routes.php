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
Route::get('start', 'Welcome@start');

Route::get('home', 'Home@index');

Route::controllers([
	'auth' => 'Auth\Auth',
	'password' => 'Auth\Password',
]);

Route::resource('users','Users',['except'=>['index','create','store','destroy']]);
Route::resource('streamers','Streamers',['only'=>['index','show']]);
Route::resource('pledges','Pledges',['except'=>['destroy','create']]);
Route::resource('users.pledges','UsersPledges', ['only'=>'index']);
Route::resource('streamers.pledges','StreamersPledges', ['only'=>'index']);
Route::resource('deposits','Deposits',['only'=>['create','store']]);

Route::controller('clients/league', 'Clients\League');

Route::get('api/v1/streamers/{username}/pledges','Api\One\StreamersPledges@index');

Route::get('privacy','Legal@privacy');
Route::get('terms','Legal@terms');

Route::match(['get','post'], 'payment/paypal/ipn/{userId}', ['as' => 'paypalIpn', 'uses' => 'PaypalPaymentController@index'])->where(['userId' => '[0-9]+']);
Route::match(['get'], 'deposit', ['as' => 'createDeposit', 'uses' => 'PaypalCreateDepositController@index']);
