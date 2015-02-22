<html>
	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="shortcut icon" href="img/favicon.ico">

		<link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
		<link href="{{ elixir("css/app.css") }}" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements. -->
	    <!--[if lt IE 9]>
			<script src="js/vendor/html5shiv.js"></script>
			<script src="js/vendor/respond.min.js"></script>
	    <![endif]-->

	    <script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
		<script data-main="{{ elixir("js/main.js") }}" src="{{ asset('js/vendor/require.js') }}"></script>

		<link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>

		<style>
			body {
				margin: 0;
				padding: 0;
				width: 100%;
				height: 100%;
				color: #B0BEC5;
				display: table;
				font-weight: 100;
				font-family: 'Lato';
			}

			.container {
				text-align: center;
				display: table-cell;
				vertical-align: middle;
			}

			.content {
				text-align: center;
				display: inline-block;
			}

			.title {
				font-size: 96px;
				margin-bottom: 40px;
			}

			.quote {
				font-size: 24px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="content">
				<div class="title">Laravel 5</div>
				<div class="quote">{{ Inspiring::quote() }}</div>
			</div>
		</div>
	</body>
</html>
