<?php


namespace AppTests\Unit\Services;

use AppTests\Functional\Controllers\IPNListenerTest;
use App\Contracts\Service\Acidifier;
use App\Models\Deposit;
use AppTests\TestCase;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Deposits;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Mockery as m;

class DepositsRepositoryTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->artisan('migrate:refresh');
    }

    public function testCreateAndDeleteAndAll()
    {
        $deposits = $this->getDepositRepo();
        $this->assertEquals(0, $deposits->all()->count());

        $d1 = $deposits->create($this->generateDataForCompleteDeposit());

        $d2 = $deposits->find($d1->id);

        $this->assertNotNull($d2);

        $this->assertEquals(1, $deposits->all()->count());

        $d1 = $deposits->create($this->generateDataForCompleteDeposit());

        $this->assertEquals(2, $deposits->all()->count());

        $d1->delete();

        //if this fails the repository probably gives a cached list
        $this->assertEquals(1, $deposits->all()->count());
    }

    public function testFindByTransactionId()
    {
        $deposits = $this->getDepositRepo();
        $this->assertEquals(0, $deposits->all()->count());

        $d1 = $deposits->create($this->generateDataForCompleteDeposit());
        $d2 = $deposits->create($this->generateDataForRefundedDeposit($d1->transaction_id));


        $this->assertEquals(2, $deposits->all()->count());
        $this->assertEquals(1, $deposits->findByTransactionId($d1->transaction_id)->count());
        $this->assertEquals(1, $deposits->findByTransactionId($d2->transaction_id)->count());
        $this->assertEquals(2, $deposits->findByTransactionId($d1->transaction_id, true, true)->count());
        $this->assertEquals(1, $deposits->findByTransactionId($d1->transaction_id, true, false)->count());
    }

    public function testGetStateGivingTransaction()
    {
        $deposits = $this->getDepositRepo();
        $this->assertEquals(0, $deposits->all()->count());

        $transaction=$this->generateDataForCompleteDeposit();
        $d1 = $deposits->create($transaction);
        $refunded=$this->generateDataForRefundedDeposit($d1->transaction_id);
        $d2 = $deposits->create($refunded);


        $this->assertEquals(2, $deposits->all()->count());
        $this->assertNull($deposits->getStateGivingDeposit($d1->transaction_id));

        $transaction=$this->generateDataForCompleteDeposit();
        $transaction['processed']=true;
        $d1 = $deposits->create($transaction);
        $this->assertNotNull($deposits->getStateGivingDeposit($d1->transaction_id));
        $this->assertEquals($d1->status, $deposits->getStateGivingDeposit($d1->transaction_id)->status);
        $this->assertEquals($d1->transaction_id, $deposits->getStateGivingDeposit($d1->transaction_id)->transaction_id);

        $refunded=$this->generateDataForRefundedDeposit($d1->transaction_id);
        $refunded['processed']=true;
        $d2 = $deposits->create($refunded);
        $this->assertNotNull($deposits->getStateGivingDeposit($d1->transaction_id));
        $this->assertEquals($d2->status, $deposits->getStateGivingDeposit($d1->transaction_id)->status);
        $this->assertEquals($d2->transaction_id, $deposits->getStateGivingDeposit($d1->transaction_id)->transaction_id);
    }


    private function generateDataForCompleteDeposit($userId = 1, $gross = 20)
    {
        return
            [
                'user_id' => $userId,
                'payment_provider' => 'paypal',
                'transaction_id' => Uuid::uuid(),
                'gross' => $gross,
                'fee' => IPNListenerTest::calculateFee($gross),
                'email' => 'no@no.no',
                'payment_date' => Carbon::now(),
                'status' => 'Completed',
                'source_message' => 'empty',
                'status_code'=>Deposit::COMPLETED_CODE,
            ];
    }

    private function generateDataForRefundedDeposit($parentTransactionId, $userId = 1, $gross = 20)
    {
        $result = $this->generateDataForCompleteDeposit($userId, $gross);
        return array_merge($result, [
            'gross' => -$gross,
            'fee' => -IPNListenerTest::calculateFee($gross),
            'status' => 'Refunded',
            'parent_transaction_id' => $parentTransactionId,
            'status_code'=>Deposit::REFUNDED_CODE,
        ]);
    }

    private function generateDataForReversedDeposit($parentTransactionId, $userId = 1, $gross = 20)
    {
        $result = $this->generateDataForCompleteDeposit($userId, $gross);
        return array_merge($result, [
            'gross' => -$gross,
            'fee' => -IPNListenerTest::calculateFee($gross),
            'status' => 'Reversed',
            'parent_transaction_id' => $parentTransactionId,
            'status_code'=>Deposit::REVERSED_CODE,
        ]);
    }

    private function generateDataForCanceledReversedDeposit($parentTransactionId, $userId = 1, $gross = 20)
    {
        $result = $this->generateDataForCompleteDeposit($userId, $gross);
        return array_merge($result, [
            'gross' => $gross,
            'fee' => IPNListenerTest::calculateFee($gross),
            'status' => 'Canceled_Reversal',
            'parent_transaction_id' => $parentTransactionId,
            'status_code'=>Deposit::CANCELED_REVERSAL_CODE,
        ]);
    }


    /**
     * @return Deposits
     */
    public function getDepositRepo()
    {
        return $this->app->make(Deposits::class);
    }
}
