@extends('app')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>Streamers</h1>
			<hr/>
			<h2>Live now</h2>
			@if (!$live->isEmpty())
				<div class="row">
					@foreach ($live as $streamer)
						<div class="col-xs-12 col-sm-3 live">
							<div class="tile">
								<a href="/streamers/{{ $streamer->id }}"><img class="avatar tile-image" src="{{ $streamer->avatar }}"/></a>
								<h3 class="tile-title"><a href="/streamers/{{ $streamer->id }}">{{ $streamer->username }}</a></h3>
								<ul>
									<li>Top pledge:
										@if ($streamer->biggestPledge)
											${{ number_format($streamer->biggestPledge,2) }}
										@else
											-
										@endif
									</li>
									<li>Average pledge: 
										@if ($streamer->averagePledge)
											${{ number_format($streamer->averagePledge,2) }}
										@else
											-
										@endif
									</li>
								</ul>
							</div>
						</div>
					@endforeach
				</div>
			@else
				<p><small>(╯°□°）╯︵ ┻━┻</small></p>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h2>Offline</h2>
			<div class="row">
				@foreach ($notLive as $streamer)
					<div class="col-xs-12 col-sm-3 not-live">
						<div class="tile">
							<a href="/streamers/{{ $streamer->id }}"><img class="avatar tile-image" src="{{ $streamer->avatar }}"/></a>
							<h3 class="tile-title"><a href="/streamers/{{ $streamer->id }}">{{ $streamer->username }}</a></h3>
							<ul>
								<li>Top pledge:
										@if ($streamer->biggestPledge)
											${{ number_format($streamer->biggestPledge,2) }}
										@else
											-
										@endif
									</li>
									<li>Average pledge: 
										@if ($streamer->averagePledge)
											${{ sprintf('%0.2f',$streamer->averagePledge) }}
										@else
											-
										@endif
									</li>
							</ul>
						</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>
@endsection
