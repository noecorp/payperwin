<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>@yield('title') - PayPerWin</title>

	<link href="{{ asset('css/vendor/bootstrap.css') }}" rel="stylesheet">
	<link href="{{ asset('css/vendor/flat-ui.min.css') }}" rel="stylesheet">
	<link href="{{ elixir("css/app.css") }}" rel="stylesheet">

	<script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
	<script src="{{ asset('js/vendor/flat-ui.min.js') }}"></script>
	<script data-main="{{ elixir("js/main.js") }}" src="{{ asset('js/vendor/require.js') }}"></script>
	
	<script async src="https://assets.helpful.io/assets/widget.js"></script>
	
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar navbar-inverse navbar-lg navbar-fixed-top" role="navigation">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-7">
			<span class="sr-only">Toggle navigation</span>
		  </button>
		  <a class="navbar-brand" href="/">PayPerWin</a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="navbar-collapse-7">
		  <ul class="nav navbar-nav">
			<li><a href="/streamers">Streamers {{ ($streamersLiveNow) ? '('.$streamersLiveNow.' live)' : '' }}</a></li>
		   </ul>
		  <ul class="nav navbar-nav navbar-right">
			@if ($auth->guest())
				<li><a href="/auth/login" >Login</a></li>
				<li><a href="/auth/register" >Register</a></li>
			@else
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $auth->user()->username }} <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="/users/{{ $auth->user()->id }}">Profile</a></li>
						<li><a href="/users/{{ $auth->user()->id }}/edit">Settings</a></li>
						<li class="divider"></li>
						<li><a href="/deposits/create">Deposit</a></li>
						@if ($auth->user()->streamer)
							<li><a href="javascript:;">Request Payout</a></li>
						@endif
						<li class="divider"></li>
						<li><a href="/auth/logout">Logout</a></li>
					</ul>
				</li>
			@endif
		  </ul>
		</div><!-- /.navbar-collapse -->
	</nav>

	<div class="container">
		@yield('content')
	</div>

	<footer>
	</footer>

	<div id="helpful">
		<a href="mailto:payperwin@helpful.io" data-helpful="payperwin" data-helpful-modal="on" class="btn btn-info">Help &amp; Feedback</a>
	</div>

</body>
</html>
