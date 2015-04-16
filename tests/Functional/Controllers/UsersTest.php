<?php namespace AppTests\Functional\Controllers;

use App\Models\User;

/**
 * @coversDefaultClass \App\Http\Controllers\Users
 */
class UsersTest extends \AppTests\TestCase {

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 */
	public function testIndexPredictablyAborts()
	{
		$response = $this->call('GET','users');

		$this->assertResponseStatus(404);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::show
	 */
	public function testShowAbortsWhenNotFound()
	{
		$response = $this->call('GET', 'users/foo');

		$this->assertResponseStatus(404);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::show
	 */
	public function testShowOkWhenFound()
	{
		User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$response = $this->call('GET', 'users/1');

		$this->assertResponseOk();
		$this->assertViewHasAll(['user','feed','stats']);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::edit
	 */
	public function testEditAbortsWhenNotAuthorized()
	{
		User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$user = User::create([
			'email' => 'bar@foo.com',
			'username' => 'bar',
		]);

		$this->be($user);

		$response = $this->call('GET', 'users/1/edit');

		$this->assertResponseStatus(401);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::edit
	 */
	public function testEditRedirectsWhenNotLoggedIn()
	{
		$response = $this->call('GET', 'users/1/edit');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::edit
	 */
	public function testEditOk()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$user = $this->be($user);

		$response = $this->call('GET', 'users/1/edit');

		$this->assertResponseOk();
		$this->assertViewHas('user',$user);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateAbortsWhenNotAuthorized()
	{
		User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$user = User::create([
			'email' => 'bar@foo.com',
			'username' => 'bar',
		]);

		$this->session(['_token'=>'foo']);

		$this->be($user);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo']);

		$this->assertResponseStatus(401);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateRedirectsWhenNotLoggedIn()
	{
		$this->session(['_token'=>'foo']);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateAbortsWithoutToken()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->session(['_token'=>'foo']);
		$this->be($user);

		$response = $this->call('PUT', 'users/1', ['foo'=>'bar']);

		$this->assertResponseStatus(418);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateAbortsWhenNotLoggedInAsAjax()
	{
		$this->session(['_token'=>'foo']);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('content-type','application/json');
		$this->assertEquals(url('auth/login'),$this->responseJson()->redirect);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateRedirectsWithErrorsWhenNotValid()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->session(['_token'=>'foo']);
		$url = url('users/1/edit');
		$this->session(['_previous.url'=>$url]);
		$this->be($user);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo','email'=>'baz']);
		
		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',$url);
		$this->assertSessionHasErrors(['email']);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateOk()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->session(['_token'=>'foo']);
		$url = url('users/1/edit');
		$this->session(['_previous.url'=>$url]);
		$this->be($user);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo','username'=>'baz']);
		
		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',$url);
		$this->assertSessionHas('success');

		$this->assertEquals(User::find(1)->username,'baz');
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::update
	 */
	public function testUpdateOkAsAjax()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->session(['_token'=>'foo']);
		$url = url('users/1/edit');
		$this->session(['_previous.url'=>$url]);
		$this->be($user);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo','username'=>'baz'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseHeaderIs('content-type','application/json');
		$this->assertTrue($this->responseJson()->success);

		$this->assertEquals(User::find(1)->username,'baz');
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 */
	public function testPredictablyAbortingWithWrongMethod()
	{
		$this->session(['_token'=>'foo']);

		$response = $this->call('POST', 'users',['_token'=>'foo']);

		$this->assertResponseStatus(404);

		$response = $this->call('DELETE', 'users/1',['_token'=>'foo']);
		
		$this->assertResponseStatus(405);
	}

}
