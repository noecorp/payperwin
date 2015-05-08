@extends('app')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>Withdraw Earnings</h1>
			<hr/>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<form role="form" id="payout-form" method="POST" action="/payout" data-ppw-percent="{{ $auth->user()->commission }}" data-ppw-flat="0">
				<input type="hidden" name="_token" value="{{ csrf_token() }}" />

				@if (count($errors->all()) || session('success') || session('error'))
					<div class="form-group">
						<div class="alert alert-{{ (session('success')) ? 'success' : 'danger' }}">
							@if (count($errors->all()))
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							@else
								{{ (session('success')) ?: session('error') }}
							@endif
						</div>
					</div>
				@endif

				<div class="form-group">
					<h2>PayPal Email</h2>
					
					<p class="help-block">
						Your earnings will be sent here.
					</p>

					<input type="text" class="form-control" id="payout-email" name="email" value="{{ (old('email')) ?: $auth->user()->email }}" />
				</div>

				<div class="form-group">
					<h2>Amount</h2>

					<div class="input-group">
						<span class="input-group-addon">$</span>
						<input type="text" class="form-control" id="payout-amount" name="amount" value="{{ (old('earnings')) ?: sprintf("%.2f",$auth->user()->earnings) }}" />
					</div>
				</div>

				<div class="form-group">
					<table class="table">
						<tr>
							<td>PayPal fee</td>
							<td id="payout-net-paypal" class="text-right">-</td>
						</tr>
						<tr>
							<td>PayPerWin fee</td>
							<td id="payout-net-ppw" class="text-right">-</td>
						</tr>
						<tr>
							<th>Total</th>
							<th id="payout-total" class="text-right">-</th>
						</tr>
					</table>
				</div>

				<div class="form-group">
					<input type="submit" class="btn btn-success btn-wide" value="Submit" />
				</div>
			</form>
		</div>
		<div class="col-xs-12 col-sm-6 col-sm-offset-2" id="payout-fees">
			<h2>Fees</h2>

			<p>There's no TL;DR here, so please take a minute to read through this.</p>

			<p>Here's the thing...</p>

			<ul>
				<li>We want to be <strong>honest</strong> with you and your fans,</li>
				<li>we need at least a bit of <strong>revenue</strong>,</li>
				<li>and we want to keep your <strong>fees</strong> minimal.</li>
			</ul>

			<p><strong>This is a very tough balance to achieve!</strong></p>

			<p>We're trying our best though, and here's how:</p>

			<ol>
				<li>
					<p>PayPal takes a cut of all fan deposits we receive on your behalf.</p>
					<p>There's no way around this and we push this on the streamer's side because pushing this on the side of your fans feels awkward:</p>
					<blockquote>"If I deposit $5 for {{ $auth->user()->username }}, I should have $5 to give to {{ $auth->user()->username }}!</blockquote>
					<p>Payment provider fees can be shouldered silently by companies only when they provide a specific product or service to the people paying. In PayPerWin's case, fans aren't buying a product or service. We're actually just keeping the payments on your behalf!</p>
					<p>Finally, this fee decreases over time as overall pledge volume (across all streamers) increases. So, after a certain point this part won't hurt as much anyway.</p>
				</li>
				<li>
					<p>On top of covering the PayPal fee, we need to charge our own commission. Otherwise, we wouldn't be making any money for our services or even covering some basic costs.</p>
					<p>At the same time, as much as we'd love to have a higher commission to drive up our revenue numbers, a major part of PayPerWin's reason for existence is to allow streamers to make more money. High commissions would go against that.</p> 
					<p>This commission is based on net earnings after PayPal's fee. Charging it on pre-fee earnings would mean you earn less. Better deal for you.</p>
					<p>The comission also varies from streamer to streamer based on affiliate performance and pledge volume. The maximum commission that we charge anyone is 3.5%, while the lowest possible is 1.5%.</p>
					<p>Your personal commission is currently <strong>{{ sprintf("%.2f", $auth->user()->commission) }}%</strong>.</p>
				</li>
				<li>We could lump the payment provider and our own fees into one larger percentage, but that would be somewhat deceptive. That path of transparency and honesty is the better path to walk. So we'd rather just lay out the fee situation and the fine balance we're trying to maintain.</li>
			</ol>
		</div>
	</div>
@endsection
