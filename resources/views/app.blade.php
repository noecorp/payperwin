<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>@yield('title') - PayPerWin</title>

	<link href="/css/vendor/all.vendor.css" rel="stylesheet">
	
	@yield('styles')

	<link href="{{ elixir("css/app.css") }}" rel="stylesheet">

	<script src="/js/vendor/all.vendor.js"></script>

	@yield('scripts')

	<script src="{{ elixir('js/all.js') }}"></script>
	
	<script async src="https://assets.helpful.io/assets/widget.js"></script>
	
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<script>
		var grooveOnLoad = function()
		{
			@if ($auth->user())
				GrooveWidget.options({
					name: "{{ $auth->user()->username }}", 
					email: "{{ $auth->user()->email }}", 
					about: "id: {{ $auth->user()->id }}"
				});
			@endif
		};
		//<![CDATA[
			(function() {var s=document.createElement('script');
			s.type='text/javascript';s.async=true;
			s.src=('https:'==document.location.protocol?'https':'http') +
			'://ppw.groovehq.com/widgets/d3ed44cc-8c5a-4569-b20e-fdcf2d74fb85/ticket.js'; var q = document.getElementsByTagName('script')[0];q.parentNode.insertBefore(s, q);})();
		//]]>
	</script>
</head>
<body>
	@if (env('APP_ENV') == 'production')
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
			ga('create', 'UA-61527064-1', 'auto');
			ga('send', 'pageview');
		</script>
	@endif
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
				<li><a href="/streamers"><span class="hidden-xs fui-video"></span> &nbsp; Streamers <small>{{ ($streamersLiveNow) ? '('.$streamersLiveNow.' live)' : '' }}</small></a></li>
				@if ($auth->user())
					<li>
						@if (!$auth->user()->start_completed)
							<a href="/start"><span class="hidden-xs fui-checkbox-checked"></span> &nbsp; Get Started</a>
						@else
							<a href="/dashboard"><span class="hidden-xs fui-list-thumbnailed"></span> &nbsp; Dashboard</a>
						@endif
					</li>
				@endif
			</ul>
			<ul class="nav navbar-nav navbar-right">
			@if ($auth->guest())
				<li><a href="/auth/login" >Login</a></li>
				<li><a href="/auth/register" >Register</a></li>
			@else
				@if ($auth->user()->streamer)
					<li><a href="/transactions">Earnings: ${{ sprintf("%0.2f",$auth->user()->earnings) }}</a></li>
				@else
					<li><a href="/transactions">Funds: ${{ sprintf("%0.2f",$auth->user()->funds) }}</a></li>
					<li><button id="nav-deposit" class="btn btn-sm btn-primary navbar-btn" type="button" data-href="/deposits/create">Deposit</button></li>
				@endif
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						@if ($auth->user()->avatar)
							<img src="/{{ $auth->user()->avatar }}" class="avatar">&nbsp;
						@endif
						{{ $auth->user()->username }} <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="/dashboard">Dashboard</a></li>
						@if ($auth->user()->streamer)
							<li><a href="/streamers/{{ $auth->user()->id }}">Profile</a></li>
						@endif
						<li><a href="/users/{{ $auth->user()->id }}/edit">Settings</a></li>
						@if ($auth->user()->streamer)
							<li class="divider"></li>
							<li><a href="/payout">Request Payout</a></li>
						@endif
						<li class="divider"></li>
						<li><a href="mailto:gg@payperwin.gg" class="support-link">Help &amp; Feedback</a></li>
						<li><a href="/auth/logout">Logout</a></li>
					</ul>
				</li>
			@endif
		  </ul>
		</div><!-- /.navbar-collapse -->
	</nav>

	<article class="@yield('container-class','container')">
		<div class="row">
			<div class="col-xs-12">
				@yield('content')
			</div>
		</div>
	</article>

	<footer>
		<nav>
			<ul>
				<li><a href="/">Home</a></li>
				<li><a href="/streamers">Streamers</a></li>
				<li><a href="mailto:gg@payperwin.gg" class="support-link">Contact Us</a></li>
				<li><a href="/privacy">Privacy Policy</a></li>
				<li><a href="/terms">Terms of Service'</a></li>
			</ul>
		</nav>
		<p>
			Copyright © 2015 Assembly Made, Inc. All rights reserved.
		</p>
		<p>
			<small>PayPerWin isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.</small>
		</p>
	</footer>
</body>
</html>
