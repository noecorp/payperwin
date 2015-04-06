<?php

namespace App\Events\Paypal\Ipn;


use App\Events\Event;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;


abstract class IPNEvent extends Event
{
    use SerializesModels;

    protected $request;

    /**
     * Create a new event instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}