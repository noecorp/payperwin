<?php


namespace AppTests\Services;


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
use Mockery as m;

class UsersRepositoryTest extends TestCase {

    public function setUp() {
        parent::setUp();
        config(['database.default'=>'sqlite_testing']);
        $this->artisan('migrate');
    }

    public function testDeleteUser() {
        $users=$this->getUsersRepo();

        $user=$users->create([]);

        $this->assertEquals(1, $users->all()->count());

        $user->delete();

        $this->assertEquals(0, $users->all()->count());
    }

    /**
     * @return Users
     */
    public function getUsersRepo()
    {
        return $this->app->make(Users::class);
    }

    public function tearDown() {
        m::close();
        parent::tearDown();
    }
}
