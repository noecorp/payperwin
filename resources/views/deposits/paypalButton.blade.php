
    <form name="_xclick" action="{{ $paypalSubmitUrl }}" method="post">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="{{ $paypalReceiver }}">
    <input type="hidden" name="currency_code" value="{{ $paypalCurrency }}">
    <input type="hidden" name="item_name" value="PayPerWin {{ $amount }}$ Top Up">
    <input type="hidden" name="amount" value="{{ $amount }}">
    <input type="hidden" name="item_number" value="payment:{{ $amount }}:user:{{ $auth->user()->id }}">
    <input type="hidden" name="custom" value="{{ $paypalCustom }}">
    <input type="hidden" name="invoice" value="{{ $paypalInvoice }}">
    <input type="hidden" name="notify_url" value="{{ $paypalIpnUrl  }}">
    <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_pponly_142x27.png" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
    </form>