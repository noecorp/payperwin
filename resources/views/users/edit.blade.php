@extends('app')

@section('title','Settings')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>Settings</h1>
			<hr/>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-5">
			<h2>Profile</h2>
			<form role="form" method="PUT" action="/users/{{ $user->id }}" id="profile-form">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_user_id" value="{{ $user->id }}">
				<div class="form-group">
					<div class="alert" style="display:none;" id="profile-results">
						<ul>
						</ul>
					</div>
				</div>
				@if (!$user->twitch_id)
					<div class="form-group">
						<p class="help-block">Connect your Twitch profile for easy login.</p>
						<a href="/auth/with/twitch" id="profile-twitch"><img src="/img/connect-twitch.png"/></a>
					</div>
				@else
					<div class="form-group">
						<p class="help-block">Connected Twitch account: </p>
						<input disabled type="text" class="form-control" value="{{ $user->twitch_username }}" />
					</div>
				@endif
				<div class="form-group">
					<p class="help-block">Your publicly-visible PayPerWin username:</p>
					<input type="text" class="form-control" id="profile-username" name="username" placeholder="Username" value="{{ (old('username')) ?: $user->username }}">
				</div>
				<div class="form-group">
					<p class="help-block">We keep your email private and use it only for sending you updates and notifications.</p>
					<input type="text" class="form-control" id="profile-email" name="email" placeholder="Email" value="{{ (old('email')) ?: $user->email }}">
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
					<button type="submit" class="btn btn-success btn-lg btn-block" id="profile-submit">Update</button>
				</div>
			</form>
		</div>
		<div class="col-xs-12 col-sm-5 col-sm-offset-2">
			<h2>Streaming</h2>
			<form role="form" method="PUT" action="/users/{{ $user->id }}" id="streaming-form">
				<input type="hidden" name="_token" value="{{ csrf_token() }}" />

				@if (!$user->streamer)
					<div class="form-group">
						<p class="help-block">If you want to earn money through PayPerWin, you need to enable your Streamer Profile.</p>
					</div>
					<div class="form-group">
						<input type="checkbox" name="streamer" data-toggle="switch" data-on-color="success" data-off-color="default" id="streamer-on" />
					</div>
				@else
					@if (!$user->summoner_id || !$user->twitch_id)
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
					<div class="form-group">
						<div class="alert" style="display:none;" id="streaming-results">
							<ul>
							</ul>
						</div>
					</div>
					@if (!$user->twitch_id)
						<div class="form-group">
							<a href="/auth/with/twitch" id="streaming-twitch"><img src="/img/connect-twitch.png"/></a>
						</div>
					@else
						<div class="form-group">
							<p class="help-block">Connected Twitch account: </p>
						<input disabled type="text" class="form-control" value="{{ $user->twitch_username }}" />
						</div>
					@endif

					@if (!$user->summoner_id)
						<div class="form-group">
							<div class="alert" style="display:none;" id="summoner-results">
								<ul>
								</ul>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-xs-6">
									<input type="hidden" name="summoner_id" id="streaming-summoner-id" value="{{ $user->summoner_id }}">
									<input type="text" class="form-control" name="summoner_name" id="streaming-summoner-name" placeholder="Summoner name" value="{{ old('summoner_name') }}">
								</div>
								<div class="col-xs-3">
									<select data-toggle="select" class="form-control select select-default" name="region" id="streaming-region">
										<optgroup label="Region">
											@foreach ($guru->regions() as $region)
												<option value="{{ $region }}">{{ strtoupper($region) }}</option>
											@endforeach
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
					@else
						<div class="form-group">
							<p class="help-block">Connected League of Legends account: </p>
							<input disabled type="text" class="form-control" value="{{ $user->summoner_name }}, {{ strtoupper($user->region) }} ({{ $user->summoner_id }})" />
						</div>
					@endif


				@endif
			</form>
			@if ($user->twitch_id && $user->summoner_id)
				<h2>Your Link</h2>
				<p>Share this URL on your stream:</p>
				@if (!$user->short_url)
					<div class="form-group">
						<input disabled type="text" class="form-control" value="{{ preg_replace('/http[s]?:\/\//','',app_url('streamers',$user->id)) }}" />
					</div>
					<div class="alert alert-info">
						<p>We'll set up a shortened URL (on the ppw.gg domain) within a few hours!</p>
					</div>
				@else
					<div class="form-group">
						<input disabled type="text" class="form-control" value="{{ preg_replace('/http[s]?:\/\//','',$user->short_url) }}" />
					</div>
					<p><em>If you want a different short url, <a href="mailto:gg@payperwin.gg" onclick="GrooveWidget.toggle(); return false;">let us know</a>.</em></p>
				@endif
				<h2>Affiliate</h2>
				<p>Invite other streamers and get a slightly lower commission for every streamer that registers with your affiliate link and completes their PayPerWin profile! Your unique affiliate URL is:
				<div class="alert alert-info">
					<a href="/?auid={{ $user->id }}">{{ app_url('/?auid='.$user->id) }}</a>
				</div>

				<h2>Commission Tiers</h2>
				<p><strong>Coming soon:</strong> Get a lower payout commission based on how many fans are making pledges. This feature will be automatically enabled for your account once implemented.</p>
			@endif
		</div>
	</div>
@endsection
