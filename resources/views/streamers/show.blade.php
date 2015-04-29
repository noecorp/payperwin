@extends('app')

@section('title', $streamer->username)

@section('content')
	<span data-model="User" data-id="{{ $streamer->id }}"></span>
	<div class="row">
		<div class="col-xs-12">
			<h1>{{ $streamer->username }}
				@if ($streamer->avatar)
					<img class="avatar" src="/{{ $streamer->avatar }}">
				@endif
			</h1>

			<hr/>

			@if ($streamer->short_url)
				Short link: <a href="{{ $streamer->short_url }}">{{ $streamer->short_url }}</a>
			@endif
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-4 col-sm-offset-4">
			@if ($auth->guest())
				<a href="/auth/login" class="btn btn-lg btn-success btn-block">Start a Pledge!</a>
			@else
				@if ($auth->user()->id != $streamer->id)
					<button type="button" id="streamer-pledge" class="btn btn-lg btn-success btn-block" title="This will bring up a form." data-placement="top" data-toggle="tooltip" data-modal="streamer-pledge-modal">Start a Pledge!</button>
				@endif
			@endif
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<h2><span class="fui-heart"></span> &nbsp; Pledges</h2>

			<ul id="pledges-list" class="pledges">
				@include('partials.pledges',['feed'=>$feed])
			</ul>
		</div>

		<div class="col-xs-12 col-sm-6">
			<h2><span class="fui-info-circle"></span> &nbsp; Stats</h2>

			<p class="lead">Top pledger: 
				@if ($stats['topPledger'])
					${{ sprintf("%0.2f",$stats['topPledger']->spent) }} <small>total</small>, {{ $stats['topPledger']->owner->username }}
				@else
					-
				@endif
			</p>

			<p class="lead"><abbr data-toggle="tooltip" data-original-title="Pledges with at least 1 donation logged">Highest</abbr> pledge: 
				@if ($stats['highestPledge'])
					${{ sprintf("%0.2f",$stats['highestPledge']->amount) }} <small>/ win</small>, {{ $stats['highestPledge']->owner->username }}
				@else
					-
				@endif
			</p>

			<p class="lead">Average pledge: 
				@if ($stats['average'])
					${{ sprintf("%0.2f",$stats['average']) }}
				@else
					-
				@endif
			</p>

			<p class="lead"><abbr data-toggle="tooltip" data-original-title="Last 20 matches">Recent</abbr> wins: 
				@if ($stats['winLoss'])
					{{ $stats['winLoss'] }}%
				@else
					-
				@endif
			</p>

			<p class="lead"><abbr data-toggle="tooltip" data-original-title="Last 20 matches">Recent</abbr> KDA: 
				@if ($stats['kda'])
					{{ sprintf("%0.2f",$stats['kda']) }}
				@else
					-
				@endif
			</p>
		</div>

		<div class="col-xs-12 col-sm-2">
			<h2><span class="fui-video"></span> &nbsp; Stream</h2>
			@if ($streamer->twitch_username)

				@if ($streamer->live)
					<p class="text-primary"><strong>Live now!</strong></p>
				@else
					<p class="text-muted"><strong>Offline</strong></p>
				@endif

				<p><a target="_blank" href="http://www.twitch.tv/{{ $streamer->twitch_username }}">http://www.twitch.tv/{{ $streamer->twitch_username }}</a></p>
				
			@endif
		</div>
	</div>

	@if ($auth->user() && $auth->user()->id != $streamer->id)
		<div class="modal fade" id="streamer-pledge-modal" tabindex="-1" role="dialog" aria-labelledby="streamer-pledge-modal-label" aria-hidden="false">
			<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="streamer-pledge-modal-label">New Pledge</h4>
				</div>
				<div class="modal-body">
					<div class="container-fluid">
						<div class="row">
							<div class="col-xs-12">
								<form id="streamer-pledge-form" role="form" method="POST" action="/pledges">
									<div class="row">
										<div class="col-xs-12">
											<input type="hidden" name="_token" value="{{ csrf_token() }}" />
											<input type="hidden" name="streamer_id" value="{{ $streamer->id }}" />
											<input type="hidden" name="type" value="{{ $guru->win() }}" />
											<input type="hidden" name="user_id" value="{{ $auth->user()->id }}" />
											<div class="form-group">
												<p class="help-block">Streamer: {{ $streamer->username }}</p>
											</div>
											<div class="form-group">
												<p class="help-block">Available funds: ${{ $auth->user()->funds }}</p>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="alert" style="display:none;" id="streamer-pledge-results">
											<ul>
											</ul>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12 col-sm-6">
											<div class="form-group">
												<p class="help-block">Amount per win:</p>
												<div class="input-group">
													<span class="input-group-addon">$</span>
													<input type="text" class="form-control" name="amount" placeholder="0.00" id="streamer-pledge-amount">
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12">
											<div class="form-group">
												<p class="help-block">Message:</p>
												<textarea class="form-control" name="message" id="streamer-pledge-text"></textarea>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12">
											<label><strong><a href="javascript:;" id="streamer-pledge-optional">Add optional limits &raquo;</a></strong></label>
										</div>
									</div>
									<div class="row" id="streamer-pledge-extras" style="display:none;">
										<div class="col-xs-12 col-sm-4">
											<div class="form-group">
												<p class="help-block">Spending limit:</p>
												<div class="input-group">
													<span class="input-group-addon">$</span>
													<input type="text" class="form-control" name="spending_limit" placeholder="0.00" id="streamer-pledge-sum">
												</div>
											</div>
										</div>
										<div class="col-xs-12 col-sm-4">
											<div class="form-group">
												<p class="help-block">Max wins:</p>
												<input type="text" class="form-control" name="win_limit" placeholder="0" id="streamer-pledge-wins">
											</div>
										</div>
										<div class="col-xs-12 col-sm-4">
											<div class="form-group">
												<p class="help-block">End date:</p>
												<input type="text" class="form-control" name="end_date" placeholder="DD-MM-YYYY" id="streamer-pledge-date" data-provide="datepicker" data-date-format="dd-mm-yyyy" data-date-start-date="{{ (new \DateTime())->setTimestamp(time() + 60 * 60 * 24)->format('d-m-Y') }}" data-date-orientation="bottom left" data-date-autoclose="true" data-date-end-date="+6m">
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="streamer-pledge-submit">Create</button>
				</div>
			</div>
		</div>
	@endif
</div>
@endsection
