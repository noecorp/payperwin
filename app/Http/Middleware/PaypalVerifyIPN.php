<?php namespace App\Http\Middleware;

use App\Events\InvalidIPNReceived;

use App\Events\Paypal\Ipn\ErrorProcessing;
use App\Events\Paypal\Ipn\WrongCurrency;
use App\Events\Paypal\Ipn\WrongCustom;
use App\Events\Paypal\Ipn\WrongReceiver;
use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PaypalVerifyIPN
{

    var $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $testing = $request->get('test_ipn', 0) == 1;

        try {
            if ($testing) {
                $response = $this->client->get(env('PAYPAL_VERIFY_URL_SANDBOX'), ['query' => $request->except(['userId'])]);
            } else {
                $response = $this->client->get(env('PAYPAL_VERIFY_URL'), ['query' => $request->except(['userId'])]);
            }

            //check if ipn message is actual a valid ipn message send by paypal
            if ('VERIFIED' !== $response->getBody()->getContents()) {
                event(new InvalidIPNReceived($request));
                abort(404);
            }
        } catch (\Exception $ex) {
            //log as urgent since an IPN can not be processed if there is an error in verifying its origin
            event(new ErrorProcessing($request, $ex));
            abort(500);
        }


        //now that this is an actual paypal ipn message check for integrity

        //check receiver email (beneficier)
        if ($request->get('receiver_email') !== env('PAYPAL_RECEIVER')) {
            event(new WrongReceiver($request));
            return response(200);
        }

        //the custom field contains ppw value for asm accounting reasons
        if ($request->get('custom') !== env('PAYPAL_CUSTOM_VALUE')) {
            event(new WrongCustom($request));
            return response(200);
        }

        //check currency
        if ($request->get('mc_currency') !== env('PAYPAL_CURRENCY')) {
            event(new WrongCurrency($request));
            return response(200);
        }

        return $next($request);
    }

}
