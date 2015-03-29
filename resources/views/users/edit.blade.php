@extends('app')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>Settings</h1>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-5">
			<form role="form" method="PUT" action="/users/{{ $user->id }}">
				<legend>Profile</legend>
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				@if (!$user->twitch_id)
					<div class="form-group">
						<p class="help-block">Connect your Twitch profile for easy login.</p>
						<a href="/auth/with/twitch" id="profile-twitch"><img src="{{ asset('img/connect-twitch.png') }}"/></a>
					</div>
				@else
					<div class="form-group">
						<p class="help-block">Connected Twitch account: {{ $user->twitch_username }}</p>
					</div>
				@endif
				@if ($errors->first('email') || $errors->first('username') || $errors->first('password') || $errors->first('password_confirmation'))
					<div class="form-group">
						<div class="alert alert-danger" >
							<strong>Whoops!</strong>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif
				<div class="form-group">
					<p class="help-block">This is the name that others will see on your PayPerWin profile:</p>
					<input type="text" class="form-control" id="profile-username" name="username" placeholder="Username" value="{{ (old('username')) ?: $user->username }}">
				</div>
				<div class="form-group">
					<p class="help-block">We keep your email private and use it only for sending you updates and notifications.</p>
					<input type="email" class="form-control" id="profile-email" name="email" placeholder="Email" value="{{ (old('email')) ?: $user->email }}">
				</div>
				<div class="form-group">
					@if ($user->password === null)
						<p class="help-block">If you set a password, you'll be able to log in with your email as well as with Twitch.</p>
					@endif
					<input type="password" class="form-control" id="profile-password" name="password" placeholder="{{ ($user->password !== null) ? 'New Password' : 'Password' }}">
				</div>
				<div class="form-group">
					<input type="password" class="form-control" id="profile-password-confirmation" name="password_confirmation" placeholder="{{ ($user->password !== null) ? 'Confirm New Password' : 'Confirm Password' }}">
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-success btn-lg btn-block">Update</button>
				</div>
			</form>
		</div>
		<div class="col-xs-12 col-sm-5 col-sm-offset-2">
			<form role="form" method="PUT" action="/users/{{ $user->id }}/edit">
				<legend>Streaming</legend>
				<input type="hidden" name="_token" value="{{ csrf_token() }}" />

				@if (!$user->streamer)
					<div class="form-group">
						<p class="help-block">If you want to earn money through PayPerWin, you need to enable your Streamer Profile.</p>
					</div>
					<div class="form-group">
						<input type="checkbox" name="streamer" data-toggle="switch" data-on-color="success" data-off-color="default" id="streamer-on" />
					</div>
				@else
					@if ($errors->first('summoner_id') || $errors->first('region'))
						<div class="form-group">
							<div class="alert alert-danger">
								<strong>Whoops!</strong>
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						</div>
					@elseif (!$user->summoner_id || !$user->twitch_id)
						<div class="form-group">
							<div class="alert alert-warning">
								Your streamer profile is not yet complete...
								<ul>
									@if (!$user->twitch_id)
										<li>You need to connect your Twitch account.</li>
									@endif
									@if (!$user->summoner_id)
										<li>You need to connect your main League of Legends account.</li>
									@endif
								</ul>
							</div>
						</div>
					@endif

					@if (!$user->twitch_id)
						<div class="form-group">
							<a href="/auth/with/twitch" id="streaming-twitch"><img src="{{ asset('img/connect-twitch.png') }}"/></a>
						</div>
					@else
						<div class="form-group">
							<p class="help-block">Connected Twitch account: {{ $user->twitch_username }}</p>
						</div>
					@endif

					@if (!$user->summoner_id)
						<div class="form-group">
							<div class="row">
								<div class="col-xs-6">
									<input type="hidden" name="summoner_id" id="streaming-summoner-id" value="{{ $user->summoner_id }}">
									<input type="text" class="form-control" name="summoner_name" id="streaming-summoner-name" placeholder="Summoner name" value="{{ old('summoner_name') }}">
								</div>
								<div class="col-xs-3">
									<select data-toggle="select" class="form-control select select-default" name="region" id="streaming-region">
										<optgroup label="Region">
											<option value="na">NA</option>
											<option value="euw">EUW</option>
											<option value="eune">EUNE</option>
											<option value="oce">OCE</option>
											<option value="br">BR</option>
											<option value="kr">KR</option>
											<option value="lan">LAN</option>
											<option value="las">LAS</option>
											<option value="tr">TR</option>
											<option value="ru">RU</option>
										</optgroup>
									</select>
								</div>
								<div class="col-xs-3">
								<button class="btn btn-info btn-block" type="button" id="streaming-search">Search</button>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="alert alert-info" id="streaming-match-info" style="display:none;">
								<p>Last match:</p>
								<ul>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<button type="submit" id="streaming-form-submit" class="btn btn-success btn-lg btn-block" disabled>Complete</button>
						</div>
					@endif
				@endif
			</form>
		</div>
	</div>
@endsection