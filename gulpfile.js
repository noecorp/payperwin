var elixir = require('laravel-elixir');
var gulp = require('gulp');
var del = require('del');

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
elixir.extend('cleanup', function(dir, except) {

    var patterns = [dir.replace(/\/+$/,'')+'/**'];
    var task = 'cleanup:'+dir+':';

    if (except != undefined)
    {
        task += except.toString();

        except.each(function(pattern) {
            return "!"+pattern;
        });
        
        patterns = patterns.concat(except);
    }

    gulp.task(task, function(cb) {
        del(patterns,cb);
    });

    return this.queueTask(task);

 });

 elixir(function(mix) {
    mix.cleanup('public/build')
        .cleanup('public/css')
        .cleanup('public/fonts')
        .cleanup('public/js')
        .cleanup('public/img')
 });

 elixir(function(mix) {
    mix.copy(
            'resources/assets/img/',
            'public/img/'
        );
 });

elixir(function(mix) {
    mix.sass()
    	.copy(
    		'bower_components/bootstrap-sass-official/assets/fonts/',
    		'public/css/fonts/'
    	)
        .copy(
            'bower_components/flat-ui/dist/fonts/',
            'public/css/fonts/'
        )
        .copy(
            'bower_components/flat-ui/dist/css/flat-ui.min.css',
            'public/css/vendor/flat-ui.min.css'
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
        )
        .copy(
            'bower_components/flat-ui/dist/js/flat-ui.min.js',
            'public/js/vendor/flat-ui.min.js'
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