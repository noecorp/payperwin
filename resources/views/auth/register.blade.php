@extends('app')

@section('content')
<div class="row">
	<div class="col-md-4 col-xs-12 col-md-offset-4">
		<form role="form" method="POST" action="/auth/register">
	        <legend>Register</legend>
	        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	        <div class="form-group">
		        <p class="help-block">You can use your Twitch account to login instantly.</p>
	        	<a href="/auth/with/twitch" id="register-twitch"><img src="{{ asset('img/connect-twitch.png') }}"/></a>
	        </div>
	        <div class="form-group">
		    	@if (count($errors) > 0)
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
		        <p class="help-block">Or just create a standard account...</p>
			</div>
	        <div class="form-group">
	            <input type="text" class="form-control" id="register-username" name="username" placeholder="Username" value="{{ old('username') }}">
	        </div>
	        <div class="form-group">
	            <input type="email" class="form-control" id="register-email" name="email" placeholder="Email" value="{{ old('email') }}">
	        </div>
	        <div class="form-group">
	            <input type="password" class="form-control" id="register-password" name="password" placeholder="Password">
	        </div>
	        <div class="form-group">
	            <input type="password" class="form-control" id="register-password-confirmation" name="password_confirmation" placeholder="Confirm Password">
	        </div>
	        <div class="form-group">
	            <label class="checkbox" for="register-agree">
		            <input type="checkbox" {{ (old('terms') || !old('email')) ? 'checked' : '' }} data-toggle="checkbox" value="1" name="terms" id="register-agree">
		            I agree to the <a href="/terms">Terms &amp; Conditions</a>
				</label>
	        </div>
	        <div class="form-group">
	            <button type="submit" class="btn btn-success btn-lg btn-block">Start!</button>
			</div>
	    </form>
	</div>
</div>
@endsection
