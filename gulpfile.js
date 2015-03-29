var elixir = require('laravel-elixir');
var gulp = require('gulp');
var del = require('del');

elixir.config.sourcemaps = false;

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

/**
 * Stylesheets-related work.
 *
 * - SASS processing
 * - Fonts
 * - Vendor css
 */
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

/**
 * Javascript-related work.
 *
 * - CoffeeScript processing
 * - Vendor js
 * - Concatenating scripts
 */
elixir(function(mix) {
    mix.coffee('resources/assets/coffee/**/*.coffee','public/js',{bare:true})
    	.copy(
            'bower_components/jquery/dist/',
            'public/js/vendor/'
        )
        .copy(
            'bower_components/flat-ui/dist/js/flat-ui.min.js',
            'public/js/vendor/flat-ui.min.js'
        )
        .scripts([
            'app.js',
            'app/Controllers/Deposits.js',
            'app/Controllers/Users.js',
            'app/Controllers/Auth.js',
            'init.js'
        ], 'public/js','public/js');
});

// Version app files
elixir(function(mix) {
    mix.version([
    	'css/app.css',
    	'js/all.js'
    ]);
});