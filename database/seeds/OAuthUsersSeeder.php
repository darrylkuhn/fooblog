<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class OAuthUsersSeeder extends Seeder
{
    public function run()
    {
        DB::table('oauth_users')->insert(
            array(
            'username' => "dkuhn",
            'password' => "password",
            'first_name' => "Darryl",
            'last_name' => "Kuhn",
            )
        );
    }
}