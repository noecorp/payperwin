@extends('app')

@section('container-class','container-fluid')

@section('content')
	<div class="row welcome text-center">
		<div class="col-xs-12 col-sm-6 col-sm-offset-3">
			<h1>Challenge and support your favorite streamer!</h1>
			<p class="lead">Fans are empowered to create &amp; fund challenges that reward streamers' in-game performance. Streamers get additional motivation to thrill their viewers with epic wins!</p>
			<p><a href="/auth/register" class="btn btn-lg">Get started!</a></p>
		</div>
	</div>
	<div class="row features">
		<div class="col-xs-12 col-sm-3 col-sm-offset-2">
			<div class="text-center">
				<h2><span class="fui-user"></span> Fans</h2>
			</div>
			<p>Using PayPerWin to support your favorite streamers lets you:</p>
			<ul>
				<li>Easily create and fund skill-based challenges.</li>
				<li>Tie donations to performance.</li>
				<li>Set up pledges in a few clicks and the system does the rest!</li>
				<li>Securely top up your account through PayPal.</li>
			</ul>
		</div>
		<div class="col-xs-12 col-sm-3 col-sm-offset-2">
			<div class="text-center">
				<h2><span class="fui-video"></span> Streamers</h2>
			</div>
			<p>Integrating PayPerWin into your stream has numerous benefits:</p>
			<ul>
				<li>Additional revenue that’s tied directly to your performance - get rewarded for your victories!</li>
				<li>Accurate, automatic earnings distribution system based on official game APIs.</li>
				<li>Increased audience engagement and involvement.</li>
				<li>Helps viewers develop a greater sense of investment in your content.</li>
				<li>Acts as a fun way to attract more viewers to your stream.</li>
			</ul>
		</div>
	</div>
	<div class="row cta-bottom">
		<div class="col-xs-8 col-xs-offset-2 col-sm-4 col-sm-offset-4">
			<a href="/auth/register" class="btn btn-lg btn-success btn-block">Sign up in 10 seconds.</a>
		</div>
	</div>
@endsection