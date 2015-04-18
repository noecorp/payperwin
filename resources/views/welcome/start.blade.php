@extends('app')

@section('content')
	<div class="row start">
		<div class="col-xs-12">
			<h1 class="text-center">You're awesome. </h1>
			<hr/>
			<p class="text-center lead">Here are your next steps to get started on PayPerWin:</p>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-5">
			<div class="todo" data-action="{{ (!$auth->user()->funds) ? url('deposits/create') : url('streamers') }}">
				<div class="todo-search text-center text-uppercase">
					<h2>Fans</h2>
				</div>
				<ul>
					<li class="todo-done">
						<div class="todo-icon fui-lock"></div>
						<div class="todo-content">
							<h4 class="todo-name">Register</h4>
							You're in.
						</div>
					</li>
					<li class="{{ ($auth->user()->funds) ? 'todo-done' : '' }}">
						<div class="todo-icon fui-paypal"></div>
						<div class="todo-content">
							<h4 class="todo-name">Deposit</h4>
							You'll need some funds!
						</div>
					</li>
					<li>
						<div class="todo-icon fui-heart"></div>
						<div class="todo-content">
							<h4 class="todo-name">Pledge</h4>
							Visit your favorite streamer's profile.
						</div>
					</li>
					<li>
						<div class="todo-icon fui-video"></div>
						<div class="todo-content">
							<h4 class="todo-name">Spectate</h4>
							Encourage the streamer!
						</div>
					</li>
				</ul>
			</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-sm-offset-2">
			<div class="todo" data-action="{{ (!$auth->user()->twitch_id || !$auth->user()->summoner_id) ? url('users/'.$auth->user()->id.'/edit') : url('streamers',[$auth->user()->id]) }}">
				<div class="todo-search text-center text-uppercase">
					<h2>Streamers</h2>
				</div>
				<ul>
					<li class="todo-done">
						<div class="todo-icon fui-lock"></div>
						<div class="todo-content">
							<h4 class="todo-name">Register</h4>
							You're in.
						</div>
					</li>
					<li class="{{ ($auth->user()->twitch_id && $auth->user()->summoner_id) ? 'todo-done' : '' }}">
						<div class="todo-icon fui-gear"></div>
						<div class="todo-content">
							<h4 class="todo-name">Setup</h4>
							Link your League of Legends summoner name.
						</div>
					</li>
					<li>
						<div class="todo-icon fui-chat"></div>
						<div class="todo-content">
							<h4 class="todo-name">Share</h4>
							Tell your viewers about your PayPerWin profile.
						</div>
					</li>
					<li>
						<div class="todo-icon fui-video"></div>
						<div class="todo-content">
							<h4 class="todo-name">Stream</h4>
							Win games and start earning!
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
@endsection
