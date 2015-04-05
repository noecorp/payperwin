<?php namespace AppTests\Functional\Controllers;

class UsersPledgesTest extends \AppTests\TestCase {

	public function testIndexOkWithNoPledges()
	{
		$response = $this->call('GET','users/1/pledges');

		$this->assertResponseOk();
		// $this->assertViewHas('pledges');
	}

	public function testIndexOkWithPledges()
	{
		$response = $this->call('GET','users/2/pledges');

		$this->assertResponseOk();
		// $this->assertViewHas('pledges');
	}

	public function testIndexAbortsWhenNotFound()
	{
		$response = $this->call('GET','users/foo/pledges');

		$this->assertResponseStatus(404);
	}

}
