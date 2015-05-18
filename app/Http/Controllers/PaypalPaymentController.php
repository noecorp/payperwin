<?php namespace App\Http\Controllers;

use App\Contracts\Repository\Deposits;
use App\Contracts\Repository\Users;
use App\Contracts\Service\Acidifier as AcidifierInterface;
use App\Events\Paypal\Ipn\DuplicateMessage;
use App\Events\Paypal\Ipn\ErrorProcessing;
use App\Events\Paypal\Ipn\FundsChanged;
use App\Events\Paypal\Ipn\SkippedMessage;
use App\Events\Paypal\Ipn\TransactionNotFound;
use App\Events\Paypal\Ipn\UserNotFound;
use App\Events\Paypal\Ipn\ValidIPNReceived;
use App\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaypalPaymentController extends Controller
{
    const COMPLETED = "Completed";
    const REFUNDED = "Refunded";
    const CANCELED_REVERSAL = "Canceled_Reversal";
    const REVERSED = "Reversed";

    public function __construct()
    {
        //require verification middleware before processing ipn message
        $this->middleware('paypal.verify.ipn');
    }

    public function index($userId, Request $request, Deposits $deposits, Users $users, AcidifierInterface $acid)
    {
        //TODO instead of issuing error 500 and relying on paypal to resend message we can reschedule this request ourself

        try {
            $user = $users->find($userId);
        } catch (\Exception $ex) {
            event(new UserNotFound($request));
            return response(200);
        }

        //verified ipn that belongs to the right payment account
        event(new ValidIPNReceived($request));

        //lock this section in order to keep a consistent transaction state (a lock is created per user, so that ipns for different users can be processed in parallel)
        @mkdir(storage_path() . "/app/locks/ipn/", 0755, true);
        touch(storage_path() . '/app/locks/ipn/' . $user->id . '.lock');
        $fp = fopen(storage_path() . '/app/locks/ipn/' . $user->id . '.lock', 'r+');

        //try to aquicre lock
        if (!flock($fp, LOCK_EX)) {
            abort(500);
        }

        try {
            //check duplicate message
            $deposit = $deposits->findByTransactionId($request->get('txn_id'))->where('status', $request->get('payment_status'))->first();

            //if duplicate and already processed then skip
            if ($deposit && $deposit->processed) {
                event(new DuplicateMessage($request));
                return response(200);
            }

            $data = [
                'status' => $request->get('payment_status'),
                'user_id' => $userId,
                'payment_provider' => 'Paypal',
                'transaction_id' => $request['txn_id'],
                'parent_transaction_id' => $request['parent_txn_id'],
                'gross' => $request['mc_gross'],
                'fee' => $request['mc_fee'],
                'email' => $request['payer_email'],
                'payment_date' => Carbon::createFromFormat('H:i:s M d, Y T', $request['payment_date']),
                'source_message' => json_encode($request->all()),
                'processed' => false,
                'status_code' => $this::getStatusCode($request->get('payment_status')),
            ];

            //if not existent create
            if (!$deposit) {
                //store ipn message and mark as unprocessed (in case of error we know which ones are to be processed later if not superseded
                //store ipn deposit message
                $deposit = $deposits->create($data);
            }

            //if not an important ipn message just store it but take no action
            if (!$this::isImportantMessage($request->get('payment_status'))) {
                //just store
                $deposit->processed = true;
                $deposit->save();
                event(new SkippedMessage($request));
                return response(200);
            }

            try {
                //transaction that saves the deposit to the database and also updates the user's funds
                $acid->transaction(function () use ($user, $deposits, $users, $data, $deposit, $request) {
                    //get current state of parent transaction in case this is a refund, canceled reversal or a reversal
                    if (!empty($data['parent_transaction_id'])) {
                        //get parent transaction state
                        $parentTransaction = $deposits->getStateGivingDeposit($data['parent_transaction_id']);
                        if (!$parentTransaction) {
                            //something is wrong
                            event(new TransactionNotFound($request));
                            abort(500);
                        }
                    }

                    $gross = 0;
                    $fee=0;
                    switch ($data['status']) {
                        case $this::REFUNDED:
                            if ($parentTransaction->isReversed()) {
                                //add reversed gross back to funds
                                $gross += -$parentTransaction->gross;
                            }
                            $gross += $data['gross'];
                            $fee += $data['fee'];
                            break;
                        case $this::REVERSED:
                            if ($parentTransaction->isCompleted()) {
                                //deduct reversed gross (no fee)
                                $gross += $data['gross'];
                            }
                            break;
                        case $this::CANCELED_REVERSAL:
                            if ($parentTransaction->isReversed()) {
                                //reverse reversal
                                $gross += -$parentTransaction->gross;
                            }
                            break;
                        case $this::COMPLETED:
                            //just add funds to the user account
                            $gross += $data['gross'];
                            $fee += $data['fee'];
                    }

                    //mark processed
                    $deposit->processed = true;
                    $deposit->save();

                    //update funds
                    $users->incrementAll([$user->id], 'funds', $gross);
                    $users->incrementAll([$user->id], 'fees', $fee);
                });
            } catch (\Exception $ex) {
                event(new ErrorProcessing($request, $ex));
                abort(500);
            }

        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        event(new FundsChanged($request));
        return response(200);
    }

    /**
     * Returns true if the payment status is a status that affects the funds of a user
     *
     * @param $status the paypal payment status
     * @return bool
     */
    static function isImportantMessage($status)
    {
        switch ($status) {
            case PaypalPaymentController::COMPLETED:
            case PaypalPaymentController::REFUNDED:
            case PaypalPaymentController::CANCELED_REVERSAL:
            case PaypalPaymentController::REVERSED:
                return true;
        }

        return false;
    }


    /**
     * Returns for a paypal payment status the corresponding status code of a Deposit
     *
     * @param $status the paypal payment status
     * @return int
     */
    static function getStatusCode($status)
    {
        switch ($status) {
            case PaypalPaymentController::COMPLETED:
                return Deposit::COMPLETED_CODE;
            case PaypalPaymentController::REFUNDED:
                return Deposit::REFUNDED_CODE;
            case PaypalPaymentController::CANCELED_REVERSAL:
                return Deposit::CANCELED_REVERSAL_CODE;
            case PaypalPaymentController::REVERSED:
                return Deposit::REVERSED_CODE;
            default:
                return Deposit::OTHER_CODE;
        }
    }

}
