@extends('app')

@section('title', $streamer->username)

@section('content')
	<span data-model="User" data-id="{{ $streamer->id }}"></span>
	<div class="row">
		<div class="col-xs-12">
			<h1>{{ $streamer->username }}
				@if ($streamer->avatar)
					<img class="avatar" src="{{ asset($streamer->avatar) }}">
				@endif
			</h1>
			@if ($streamer->short_url)
				Short link: <a href="{{ $streamer->short_url }}">{{ $streamer->short_url }}</a>
			@endif
			<hr/>
			@if ($streamer->live)
				<p class="text-primary"><strong>Live now!</strong></p>
			@else
				<p class="text-muted"><strong>Offline</strong></p>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			@if ($auth->guest())
				<a href="/auth/login" class="btn btn-lg btn-success btn-block">Start a Pledge!</a>
			@else
				@if ($auth->user()->id != $streamer->id)
					<button type="button" id="streamer-pledge" class="btn btn-lg btn-success btn-block" title="This will bring up a form." data-placement="top" data-toggle="tooltip" data-modal="streamer-pledge-modal">Start a Pledge!</button>
				@else
					@if ($streamer->twitch_id && $streamer->summoner_id)
						<button type="button" class="btn btn-lg btn-info btn-block">That's you!</button>
					@else
						<div class="alert alert-warning" id="streamer-pledge-results">
							<p>You haven't completed your streamer profile yet!</p>
							<p><a href="/users/{{ $streamer->id }}/edit">Finish setting up &raquo;</a></p>
						</div>
					@endif
				@endif
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-md-6">
			<h2 id="loading">Pledges</h2>

			<ul id="pledges-list">
				@if (!$feed->isEmpty())
					@foreach ($feed as $pledge)
						<li><a href="/users/{{ $pledge->owner->id }}">{{ $pledge->owner->username }}</a> with ${{ sprintf("%0.2f",$pledge->amount) }} per win.
							@if ($pledge->message)
								<br><blockquote>{{ $pledge->message }}</blockquote>
							@endif
						</li>
					@endforeach
				@else
					<li>No pledges yet!</li>
				@endif
			</ul>
		</div>
		<div class="col-xs-12 col-md-6">
			<h2>Stats</h2>

			<table class="table table-hover">
				<tbody>
					<tr class="success">
						<th>
							Top pledger
						</th>
						<td>
							@if ($stats['topPledger'])
								<a href="/users/{{ $stats['topPledger']->owner->id }}">{{ $stats['topPledger']->owner->username }}</a> with ${{ sprintf("%0.2f",$stats['topPledger']->spent) }} total
							@else
								-
							@endif
						</td>
					</tr>
					<tr class="warning">
						<th>
							Highest pledge
						</th>
						<td>
							@if ($stats['highestPledge'])
								${{ sprintf("%0.2f",$stats['highestPledge']->amount) }}, <a href="/users/{{ $stats['highestPledge']->owner->id }}">{{ $stats['highestPledge']->owner->username }}</a>
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>
							Average pledge
						</th>
						<td>
							@if ($stats['average'])
								${{ sprintf("%0.2f",$stats['average']) }}
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<th>
							Active pledges
						</th>
						<td>
							@if ($stats['activePledges'])
								{{ $stats['activePledges'] }}
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>
							Total pledges
						</th>
						<td>
							@if ($stats['totalPledges'])
								{{ $stats['totalPledges'] }}
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<th>
							Recent wins
						</th>
						<td>
							@if ($stats['winLoss'])
								{{ $stats['winLoss'] }}%
							@else
								-
							@endif
						</td>
					</tr>
					<tr>
						<th>
							Recent KDA
						</th>
						<td>
							@if ($stats['kda'])
								{{ sprintf("%0.2f",$stats['kda']) }}
							@else
								-
							@endif
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h2>Stream</h2>
			@if ($streamer->twitch_username)
			
			<p><a href="javascript:;" id="streamer-hide">Hide</a></p>
			
			<div class="row" id="streamer-stream">
				<div class="col-xs-12 col-sm-8">
					<div id="streamer-video" data-username="{{ $streamer->twitch_username }}">
						<img src="/img/loading.gif">
					</div>
				</div>
				<div class="col-xs-12 col-sm-4">
					<div id="streamer-chat" data-username="{{ $streamer->twitch_username }}">
				</div>
			</div>
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