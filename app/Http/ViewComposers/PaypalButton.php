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
        $view->with('paypalSubmitUrl', config('services.paypal.submit_url'));
        $view->with('paypalReceiver', config('services.paypal.receiver'));
        $view->with('paypalIpnUrl', $this->auth->check()?route('paypalIpn',['userId'=> $this->auth->user()->id]):'');
        $view->with('paypalUrl', $this->auth->check()?config('services.paypal.verify_url'):'');
        $view->with('paypalInvoice', $this->auth->check()?'user:'.$this->auth->user()->id:'');
        $view->with('paypalCustom', config('services.paypal.custom_value'));
        $view->with('paypalCurrency', config('services.paypal.currency'));
    }

}