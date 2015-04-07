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
        try {

            $response = $this->client->get(config('services.paypal.verify_url'), ['query' => $request->except(['userId'])]);

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
        if ($request->get('receiver_email') !== config('services.paypal.receiver')) {
            event(new WrongReceiver($request));
            return response(200);
        }

        //the custom field contains ppw value for asm accounting reasons
        if ($request->get('custom') !== config('services.paypal.custom_value')) {
            event(new WrongCustom($request));
            return response(200);
        }

        //check currency
        if ($request->get('mc_currency') !== config('services.paypal.currency')) {
            event(new WrongCurrency($request));
            return response(200);
        }

        return $next($request);
    }

}
