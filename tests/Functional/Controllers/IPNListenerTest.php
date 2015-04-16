<?php


namespace AppTests\Functional\Controllers;

use App\Contracts\Service\Acidifier;
use AppTests\TestCase;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Deposits;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Illuminate\Cache\NullStore;
use Illuminate\Cache\Repository;
use Mockery as m;
use Mockery;

/**
 * @coversDefaultClass \App\Http\Controllers\PaypalPaymentController
 */
class IPNListenerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected $migrate = true;

    public function setUp()
    {
        parent::setUp();

        $deposits = $this->getDepositRepo();
        $users = $this->getUsersRepo();
        $acidifier = $this->getAcidifier();

        // Create a mock subscriber and queue two responses.
        $httpMock = new Mock([
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
            new Response(200, ['X-Foo' => 'Bar'], Stream::factory("VERIFIED")),         // Use response object
        ]);

        $httpClientMock = $this->getGuzzleClientMock($httpMock);

        $this->app->instance(Deposits::class, $deposits);
        $this->app->instance(Users::class, $users);
        $this->app->instance(Acidifier::class, $acidifier);
        $this->app->instance(Client::class, $httpClientMock);

    }

    /**
     * @small
     *
     * @group controllers
     *
     * @covers ::__construct
     * @covers ::index
     */
    public function testSimpleFundsAdded()
    {
        $users=$this->app->make(Users::class);
        //create dummy user to add funds to
        $user = $users->create(['email'=>'foo']);
        $this->assertNotNull($user);

        $gross=20;
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $this->generateCompleteMessageData($gross));
        $this->assertTrue($response->isOk());

        //get updated users
        $user=$users->find($user->id);
        $this->assertEquals($gross-$this::calculateFee($gross), $user->funds);
    }

    /**
     * @small
     *
     * @group controllers
     *
     * @covers ::__construct
     * @covers ::index
     */
    public function testFundsAddedAndRefunded()
    {
        $users = $this->app->make(Users::class);
        //create dummy user to add funds to
        $user = $users->create(['email'=>'foo']);
        $this->assertNotNull($user);


        $gross = 20;
        $user = $users->create(['email'=>'bar']);
        $this->assertNotNull($user);

        $transaction = $this->generateCompleteMessageData($gross);
        $refund=$this->generateRefundedMessageData($transaction['txn_id'], $gross);

        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $transaction);
        $this->assertTrue($response->isOk());
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $refund);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals(0, $user->funds);
    }

    /**
     * @small
     *
     * @group controllers
     *
     * @covers ::__construct
     * @covers ::index
     */
    public function testFundsAddedAndReversedAndReverseCanceled()
    {
        $users = $this->app->make(Users::class);
        //create dummy user to add funds to
        $user = $users->create(['email'=>'foo']);
        $this->assertNotNull($user);


        $gross = 20;
        $user = $users->create(['email'=>'bar']);
        $this->assertNotNull($user);

        $transaction = $this->generateCompleteMessageData($gross);
        $reversed=$this->generateReversedMessageData($transaction['txn_id'], $gross);
        $reverseCanceled=$this->generateReverseCanceledMessageData($transaction['txn_id'], $gross);

        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $transaction);
        $this->assertTrue($response->isOk());
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $reversed);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals(0, $user->funds);

        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $reverseCanceled);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals($gross-$this->calculateFee($gross), $user->funds);
    }

    /**
     * @small
     *
     * @group controllers
     *
     * @covers ::__construct
     * @covers ::index
     */
    public function testFundsAddedAndReversedAndRefunded()
    {
        $users = $this->app->make(Users::class);
        //create dummy user to add funds to
        $user = $users->create(['email'=>'foo']);
        $this->assertNotNull($user);


        $gross = 20;
        $user = $users->create(['email'=>'bar']);
        $this->assertNotNull($user);

        $transaction = $this->generateCompleteMessageData($gross);
        $reversed=$this->generateReversedMessageData($transaction['txn_id'], $gross);
        $refund=$this->generateRefundedMessageData($transaction['txn_id'], $gross);


        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $transaction);
        $this->assertTrue($response->isOk());
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $reversed);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals(0, $user->funds);

        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $refund);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals(0, $user->funds);
    }

    /**
     * @small
     *
     * @group controllers
     *
     * @covers ::__construct
     * @covers ::index
     */
    public function testFundsAddedAndRefundedBeforeReversed()
    {
        $users = $this->app->make(Users::class);
        //create dummy user to add funds to
        $user = $users->create(['email'=>'foo']);
        $this->assertNotNull($user);


        $gross = 20;
        $user = $users->create(['email'=>'bar']);
        $this->assertNotNull($user);

        $transaction = $this->generateCompleteMessageData($gross);
        $reversed=$this->generateReversedMessageData($transaction['txn_id'], $gross);
        $refund=$this->generateRefundedMessageData($transaction['txn_id'], $gross);
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $transaction);
        $this->assertTrue($response->isOk());
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $refund);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals(0, $user->funds);

        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $reversed);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals(0, $user->funds);
    }

    /**
     * @small
     *
     * @group controllers
     *
     * @covers ::__construct
     * @covers ::index
     */
    public function testFundsAddedAndReverseCanceledWithoutReverse()
    {
        $users = $this->app->make(Users::class);
        //create dummy user to add funds to
        $user = $users->create(['email'=>'foo']);
        $this->assertNotNull($user);


        $gross = 20;
        $user = $users->create(['email'=>'bar']);
        $this->assertNotNull($user);

        $transaction = $this->generateCompleteMessageData($gross);
        $reverseCanceled=$this->generateReverseCanceledMessageData($transaction['txn_id'], $gross);
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $transaction);
        $this->assertTrue($response->isOk());
        $response = $this->route('POST', 'paypalIpn', ['userId' => $user->id], $reverseCanceled);
        $this->assertTrue($response->isOk());

        //get updated users
        $user = $users->find($user->id);
        $this->assertEquals($gross-$this::calculateFee($gross), $user->funds);
    }


    /**
     * @return Users
     */
    public function getUsersRepo()
    {
        return new \App\Repositories\Users($this->app);
    }

    public function getUsersRepoMock()
    {
        return m::mock(Users::class);
    }

    public function getDepositsRepoMock()
    {
        return m::mock(Deposits::class);
    }

    public function getAcidifierMock()
    {
        return m::mock(Acidifier::class);
    }

    /**
     * @return Deposits
     */
    public function getDepositRepo()
    {
        return $this->app->make(Deposits::class);
    }

    public function getGuzzleClientMock($mock)
    {
        $client = new Client();

        // Add the mock subscriber to the client.
        $client->getEmitter()->attach($mock);
        return $client;
    }

    /**
     * @return Acidifier
     */
    public function getAcidifier()
    {
        return $this->app->make(Acidifier::class);
    }

    private function generateCompleteMessageData($gross)
    {
        return [
            'txn_id' => Uuid::uuid(),
            'mc_gross' => $gross,
            'mc_fee' => $this::calculateFee($gross),
            'payment_date' => Carbon::now()->format('H:i:s M d, Y T'),
            'payment_status' => 'Completed',
            'payer_email' => 'no@no.no',
            'custom' => config('services.paypal.custom_value'),
            'mc_currency' => config('services.paypal.currency'),
            'receiver_email' => config('services.paypal.receiver'),
        ];
    }

    private function generateRefundedMessageData($parentId, $gross)
    {
        return array_merge($this->generateCompleteMessageData($gross),
            [
                'mc_gross' => -$gross,
                'mc_fee' => -$this::calculateFee($gross),
                'parent_txn_id' => $parentId,
                'payment_status'=>'Refunded',
            ]);
    }

    private function generateReversedMessageData($parentId, $gross)
    {
        return array_merge($this->generateCompleteMessageData($gross),
            [
                'mc_gross' => -$gross+$this::calculateFee($gross),
                'mc_fee' => -$this::calculateFee($gross),
                'parent_txn_id' => $parentId,
                'payment_status'=>'Reversed',
            ]);
    }


    private function generateReverseCanceledMessageData($parentId, $gross)
    {
        return array_merge($this->generateCompleteMessageData($gross),
            [
                'mc_gross' => $gross-$this::calculateFee($gross),
                'mc_fee' => $this::calculateFee($gross),
                'parent_txn_id' => $parentId,
                'payment_status'=>'Canceled_Reversal',
            ]);
    }

    public static function calculateFee($gross)
    {
        return $gross * 0.029 + 0.03;
    }
}
