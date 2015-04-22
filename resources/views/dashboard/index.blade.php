@extends('app')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>Dashboard</h1>
			<hr/>
		</div>
	</div>

	@if ($auth->user()->streamer_completed)
		<div class="row">
			<div class="col-xs-12 col-sm-5">
				<h2><span class="fui-bookmark"></span> &nbsp; Earnings</h2>

				<p class="lead">Today: <span class="text-success">${{ number_format($earnings['summary']['today'],2) }}</span></p>

				<p class="lead">This week: <span class="text-success">${{ number_format($earnings['summary']['week'],2) }}</span></p>

				<p class="lead">This month: <span class="text-success">${{ number_format($earnings['summary']['month'],2) }}</span></p>

				<p class="lead">Total: <span class="text-success">${{ number_format($earnings['summary']['total'],2) }}</span></p>	
			</div>

			<div class="col-xs-12 col-sm-7">
				<div class="ct-chart ct-golden-section" id="earnings-chart" data-labels="{{ implode(',',$earnings['history']['days']) }}" data-values="{{ implode(',',$earnings['history']['amounts']) }}"></div>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 col-sm-4">
				<h2><span class="fui-star-2"></span> &nbsp; Latest</h2>

				<ul id="pledges-list" class="pledges">
					@include('partials.pledges',['feed'=>$pledges['latest']])
				</ul>
			</div>

			<div class="col-xs-12 col-sm-4">
				<h2><span class="fui-user"></span> &nbsp; Leaderboards</h2>

				<p>
					<a class="btn btn-xs {{ ($leaderboard['type'] == 'total') ? 'btn-info' : 'btn-default' }}" href="?leaderboards=total">Total Donated</a> <a class="btn btn-xs {{ ($leaderboard['type'] == 'biggest') ? 'btn-info' : 'btn-default' }}" href="?leaderboards=biggest">Biggest Pledge</a>
				</p>

				<table class="table table-hover" style="padding-right:50px;">
					<tbody>
						@foreach ($leaderboard['leaders'] as $leader)
							<tr>
								<td>
									{{ $leader['rank'] }}
								</td>
								<td>
									{{ $leader['username'] }}
								</td>
								<td class="text-right">
									${{ number_format($leader['amount'],2) }}</a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
				
				@include('partials.pagination',['url' => 'dashboard?leaderboards='.$leaderboard['type'], 'less' => $leaderboard['less'], 'more' => $leaderboard['more']])
			</div>

			<div class="col-xs-12 col-sm-4">
				<h2><span class="fui-heart"></span> &nbsp; Pledges</h2>

				<p class="lead">Average: <span class="text-success">${{ sprintf('%.2f',$pledges['stats']['average']) }}</span></p>
				<p class="lead">Active: {{ $pledges['stats']['active'] }}</p>
				<p class="lead">Total: {{ $pledges['stats']['active'] }}</p>

				<h3>New</h3>
				
				<p>Today: {{ $pledges['stats']['new-today'] }}</p>
				<p>This Week: {{ $pledges['stats']['new-week'] }}</p>
				<p>This Month: {{ $pledges['stats']['new-month'] }}</p>
			</div>
		</div>
	@endif

	@if (!$pledges['active']->isEmpty() || !$pledges['inactive']->isEmpty())
		<div class="col-xs-12 col-sm-5">
			<h2><span class="fui-heart"></span> &nbsp; Pledged</h2>

			@if (!$pledges['active']->isEmpty())
				<h3>Active</h3>

				<ul id="pledges-active" class="pledges">
					@include('partials.pledges',['feed'=>$pledges['active'], 'pledgesFor' => 'streamer'])
				</ul>
			@endif

			@if (!$pledges['inactive']->isEmpty())
				<h3>Stopped</h3>

				<ul id="pledges-inactive" class="pledges">
					@include('partials.pledges',['feed'=>$pledges['inactive'], 'pledgesFor' => 'streamer'])
				</ul>
			@endif
		</div>

		<div class="col-xs-12 col-sm-7">
			<div class="ct-chart ct-golden-section" id="spending-chart" data-labels="{{ implode(',',$spending['history']['days']) }}" data-values="{{ implode(',',$spending['history']['amounts']) }}"></div>
		</div>
	@endif
@endsection
