var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass()
    	.copy(
    		'bower_components/bootstrap-sass-official/assets/fonts/',
    		'public/fonts/'
    	);
});

elixir(function(mix) {
    mix.coffee()
    	.copy(
            'bower_components/jquery/dist/',
            'public/js/vendor/'
        )
        .copy(
            'bower_components/requirejs/require.js',
            'public/js/vendor/require.js'
        )
        .copy(
            'bower_components/requirejs-text/text.js',
            'public/js/vendor/text.js'
        )
        .copy(
            'bower_components/requirejs-router/router.js',
            'public/js/vendor/router.js'
        )
        .copy(
            'bower_components/requirejs-domready/domReady.js',
            'public/js/vendor/domReady.js'
        );
});

// Version app files
elixir(function(mix) {
    mix.version([
    	'css/app.css',

    	'js/app/Controllers/*',
    	'js/app/Models/*',
    	'js/app/Routes/*',
    	'js/app/Support/*',

    	'js/main.js'
    ]);
});