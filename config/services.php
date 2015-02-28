<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => [
		'domain' => '',
		'secret' => '',
	],

	'mandrill' => [
		'secret' => '',
	],

	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],

	'stripe' => [
		'model'  => 'User',
		'secret' => '',
	],

	'twitch' => [
		'client_id' => env('TWITCH_KEY'),
		'client_secret' => env('TWITCH_SECRET'),
		'redirect' => env('TWITCH_REDIRECT_URI'),
	],

	'facebook' => [
		'client_id' => env('FACEBOOK_KEY'),
		'client_secret' => env('FACEBOOK_SECRET'),
		'redirect' => env('FACEBOOK_REDIRECT_URI'),
	],

];
