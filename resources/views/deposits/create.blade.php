@extends('app')

@section('title', 'New Deposit')

@section('content')
	<div class="row">
		<div class="col-sm-6 col-xs-12 col-sm-offset-3">
			<h1>Deposit Funds</h1>
			<hr/>
			<div class="alert alert-info">
				No actual payment will be made while we're still in development. This will simply top up your account with the specified amount.
			</div>
			@if (!$errors->isEmpty())
				<div class="alert alert-danger">
					Whoops...
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@elseif (Session::has('success'))
				<div class="alert alert-success">
					{{ Session::get('success') }}
				</div>
			@endif
			<form role="form" method="POST" action="/deposits">
				<input type="hidden" name="_token" value="{{ csrf_token() }}"/>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">$</span>
						<input type="text" class="form-control" name="amount" placeholder="0.00" />
						<span class="input-group-btn"><input type="submit" class="btn btn-default" value="Top up with PayPal"></span>
					</div>
				</div>
			</form>

			{{--
			@include('deposits.paypalButton', ['amount'=>20, 'sandbox'=>true])
			@include('deposits.paypalButton', ['amount'=>50, 'sandbox'=>true])
			--}}

		</div>
	</div>
@endsection