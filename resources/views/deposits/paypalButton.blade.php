@if (Auth::check())
    <form name="_xclick" action="{{ $paypalUrl(isset($sandbox)?$sandbox:true) }}" method="post">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="{{ $paypalReceiver }}">
    <input type="hidden" name="currency_code" value="{{ $paypalCurrency }}">
    <input type="hidden" name="item_name" value="payperwin {{ $amount or 20}}$">
    <input type="hidden" name="amount" value="{{ $amount or 20}}">
    <input type="hidden" name="item_number" value="payment:{{ $amount or 20 }}:user:{{ Auth::id() }}">
    <input type="hidden" name="custom" value="{{ $paypalCustom }}">
    <input type="hidden" name="invoice" value="{{ $paypalInvoice }}">
    <input type="hidden" name="notify_url" value="{{ $paypalIpnUrl  }}">
    <input type="image" src="http://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
    </form>
@else
    You are not signed in
@endif