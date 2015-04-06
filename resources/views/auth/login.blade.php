@extends('app')

@section('title','Login')

@section('content')
<div class="row">
	<div class="col-md-4 col-xs-12 col-md-offset-4">
		<form role="form" method="POST" action="/auth/login">
	        <legend>Login</legend>
	        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	        <div class="form-group">
		        <p class="help-block">You can use your Twitch account to login instantly.</p>
	        	<a href="/auth/with/twitch" id="login-twitch"><img src="{{ asset('img/connect-twitch.png') }}"/></a>
	        </div>
	        <div class="form-group">
		    	@if (!$errors->isEmpty())
					<div class="alert alert-danger">
						<strong>Whoops!</strong>
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif
	        </div>
	        <div class="form-group">
		        <p class="help-block">Or just use your email and password...</p>
	        </div>
	        <div class="form-group">
	            <input type="email" class="form-control" id="login-email" name="email" placeholder="Email" value="{{ old('email') }}">
	        </div>
	        <div class="form-group">
	            <input type="password" class="form-control" id="login-password" name="password" placeholder="Password">
	        </div>
	        <div class="form-group">
	            <label class="checkbox" for="login-remember">
		            <input type="checkbox" {{ (old('remember') || !old('email')) ? 'checked' : '' }} data-toggle="checkbox" value="1" name="remember" id="login-remember">
		            Keep me signed in
				</label>
	        </div>
	        <div class="form-group">
	            <button type="submit" class="btn btn-success btn-lg btn-block">Go</button>
			</div>
			<div class="form-group">
				<p class="help-block"><small><a href="/auth/forgot">Forgot your password?</a></small></p>
			</div>
	    </form>
	</div>
</div>
@endsection
