<?php namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Auth\Guard;


class PaypalButton
{

    /**
     * The authentication service implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new wildcard view composer.
     *
     * @param  Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $auth = $this->auth;
        $view->with('paypalReceiver', '');
        $view->with('paypalIpnUrl', $auth->check()?route('paypalIpn',['userId'=> $auth->user()->id]):'');
        $view->with('paypalUrl', function ($testing=true) use ($auth) {
            if ($auth->check()) {
                return $testing?env('PAYPAL_VERIFY_URL_SANDBOX'):env('PAYPAL_VERIFY_URL');
            }
            return '';
        });
        $view->with('paypalInvoice', $auth->check()?'user:'.$auth->user()->id:'');
        $view->with('paypalCustom', env('PAYPAL_CUSTOM_VALUE'));
        $view->with('paypalCurrency', env('PAYPAL_CURRENCY'));
    }

}