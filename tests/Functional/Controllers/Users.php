<?php namespace AppTests\Functional\Controllers;

use App\Models\User;

class Users extends \AppTests\TestCase {

	public function testIndexPredictablyAborts()
	{
		$response = $this->call('GET','users');

		$this->assertResponseStatus(404);
	}

	public function testShowAbortsWhenNotFound()
	{
		$response = $this->call('GET', 'users/foo');

		$this->assertResponseStatus(404);
	}

	public function testShowOkWhenFound()
	{
		$response = $this->call('GET', 'users/1');

		$this->assertResponseOk();
		$this->assertViewHasAll(['user','feed','stats']);
	}

	public function testEditAbortsWhenNotAuthorized()
	{
		$this->become(2);

		$response = $this->call('GET', 'users/1/edit');

		$this->assertResponseStatus(401);
	}

	public function testEditRedirectsWhenNotLoggedIn()
	{
		$response = $this->call('GET', 'users/1/edit');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	public function testEditOk()
	{
		$user = $this->become(1);

		$response = $this->call('GET', 'users/1/edit');

		$this->assertResponseOk();
		$this->assertViewHas('user',$user);
	}

	public function testUpdateAbortsWhenNotAuthorized()
	{
		$this->session(['_token'=>'foo']);
		$this->become(2);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo']);

		$this->assertResponseStatus(401);
	}

	public function testUpdateRedirectsWhenNotLoggedIn()
	{
		$this->session(['_token'=>'foo']);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	public function testUpdateAbortsWithoutToken()
	{
		$this->session(['_token'=>'foo']);
		$this->become(1);

		$response = $this->call('PUT', 'users/1', ['foo'=>'bar']);

		$this->assertResponseStatus(418);
	}

	public function testUpdateAbortsWhenNotLoggedInAsAjax()
	{
		$this->session(['_token'=>'foo']);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('content-type','application/json');
		$this->assertEquals(url('auth/login'),$this->responseJson()->redirect);
	}

	public function testUpdateRedirectsWithErrorsWhenNotValid()
	{
		$this->session(['_token'=>'foo']);
		$url = url('users/1/edit');
		$this->session(['_previous.url'=>$url]);
		$this->become(1);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo','email'=>'baz']);
		
		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',$url);
		$this->assertSessionHasErrors(['email']);
	}

	public function testUpdateOk()
	{
		$this->session(['_token'=>'foo']);
		$url = url('users/1/edit');
		$this->session(['_previous.url'=>$url]);
		$this->become(1);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo','username'=>'baz']);
		
		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',$url);
		$this->assertSessionHas('success');

		$this->assertEquals(User::find(1)->username,'baz');
	}

	public function testUpdateOkAsAjax()
	{
		$this->session(['_token'=>'foo']);
		$url = url('users/1/edit');
		$this->session(['_previous.url'=>$url]);
		$this->become(1);

		$response = $this->call('PUT', 'users/1',['_token'=>'foo','username'=>'baz'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseHeaderIs('content-type','application/json');
		$this->assertTrue($this->responseJson()->success);

		$this->assertEquals(User::find(1)->username,'baz');
	}

	public function testPredictablyAbortingWithWrongMethod()
	{
		$this->session(['_token'=>'foo']);

		$response = $this->call('POST', 'users',['_token'=>'foo']);

		$this->assertResponseStatus(404);

		$response = $this->call('DELETE', 'users/1',['_token'=>'foo']);
		
		$this->assertResponseStatus(405);
	}

}
