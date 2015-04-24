@extends('app')

@section('title', 'Transactions')

@section('content')
	<h1>Transactions</h1>
	<hr/>
	<div class="row">
		<div class="col-xs-12 col-sm-12">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Type</th>
						<th></th>
						<th class="text-right">Amount</th>
						<th class="text-right">Date</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($transactions as $transaction)
						<tr>
							<td>
								@if ($transaction->transaction_type == $guru->pledgeTaken())
									Pledge paid
								@elseif ($transaction->transaction_type == $guru->pledgePaid())
									Pledge earned
								@elseif ($transaction->transaction_type == $guru->fundsDeposited())
									Funds deposited
								@elseif ($transaction->transaction_type == $guru->streamerPaidOut())
									Earnings paid out
								@endif
							</td>
							<td>
								{{ $transaction->username }}
							</td>
							<td class="text-right">
								{{ (in_array($transaction->transaction_type, [$guru->pledgePaid(), $guru->fundsDeposited()])) ? '+' : '-' }} ${{ sprintf("%0.2f",$transaction->amount) }}
							</td>
							<td class="text-right">
								<small>{{ $transaction->created_at->diffForHumans() }}</small>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	@include('partials.pagination',['url' => 'transactions', 'less' => $less, 'more' => $more])

@endsection
