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
		'secret' => env('MANDRILL_KEY'),
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

	'paypal' => [
		'submit_url' => env('PAYPAL_SUBMIT_URL'),
		'receiver' => env('PAYPAL_RECEIVER'),
		'custom_value' => env('PAYPAL_CUSTOM_VALUE'),
		'currency' => env('PAYPAL_CURRENCY'),
		'verify_url' => env('PAYPAL_VERIFY_URL'),
	],

	'riot' => [
		'key' => env('RIOT_KEY'),
	],

];
