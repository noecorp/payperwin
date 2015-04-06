<?php namespace App\Events\Paypal\Ipn;


use Illuminate\Http\Request;


class ErrorProcessing extends IPNEvent
{

    protected $exception;

    /**
     * Create a new event instance.
     *
     * @param Request $request
     * @param \Exception $ex
     */
    public function __construct(Request $request, \Exception $ex)
    {
        parent::__construct($request);
        $this->exception = $ex;
    }

}
