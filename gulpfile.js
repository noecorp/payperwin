var elixir = require('laravel-elixir');
var gulp = require('gulp');
var del = require('del');

elixir.config.sourcemaps = false;

elixir.extend('cleanup', function(dir) {

	var patterns = [dir.replace(/\/+$/,'')+'/**'];
	var task = 'cleanup:'+dir+':';

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
		.cleanup('public/img');
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
 * - LESS processing
 * - Fonts
 * - Vendor css
 */
elixir(function(mix) {
	mix.sass()
		.less()
		.copy(
			'bower_components/bootstrap-sass-official/assets/fonts/',
			'public/css/fonts/'
		)
		.copy(
			'bower_components/flat-ui/dist/fonts/',
			'public/css/fonts/'
		)
		.copy(
			'bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.css',
			'public/css/vendor/bootstrap.datepicker.css'
		)
		.styles([
			'public/css/vendor/bootstrap.css',
			'public/css/vendor/flat-ui.css',
			'public/css/vendor/bootstrap.datepicker.css',
			'public/css/vendor/chartist.css'
		], 'public/css/vendor/all.vendor.css', 'public/css')
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
			'bower_components/jquery/dist/jquery.js',
			'public/js/vendor/jquery.js'
		)
		.copy(
			'bower_components/flat-ui/dist/js/flat-ui.js',
			'public/js/vendor/flat-ui.js'
		)
		.copy(
			'bower_components/bootstrap-sass-official/assets/javascripts/bootstrap/modal.js',
			'public/js/vendor/bootstrap.modal.js'
		)
		.copy(
			'bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
			'public/js/vendor/bootstrap.datepicker.min.js'
		)
		.copy(
			'resources/assets/js/vendor/jquery.scrollintoview.js',
			'public/js/vendor/jquery.scrollintoview.js'
		)
		.copy(
			'bower_components/jquery-cookie/jquery.cookie.js',
			'public/js/vendor/jquery.cookie.js'
		)
		.copy(
			'bower_components/chartist/dist/chartist.js',
			'public/js/vendor/chartist.js'
		)
		.scripts([
			'public/js/vendor/jquery.js',
			'public/js/vendor/flat-ui.js',
			'public/js/vendor/bootstrap.modal.js',
			'public/js/vendor/bootstrap.datepicker.min.js',
			'public/js/vendor/jquery.scrollintoview.js',
			'public/js/vendor/jquery.cookie.js',
			'public/js/vendor/chartist.js',
		], 'public/js/vendor/all.vendor.js', 'public/js')
		.scripts([
			'app.js',
			'app/Controllers/*',
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
