<?php

class UserTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testDomainAttribute()
	{
		$user = New Fooblog\User;
		$user->email = 'foo@fooblog.com';

		$this->assertEquals('fooblog.com', $user->domain);
	}

}
