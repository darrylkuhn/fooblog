<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Fooblog\User;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        User::create(
            [
            'name' => 'Foo Blogger', 
            'email' => 'foo@fooblog.com',
            'password' => 'password']
        );

        $faker = Faker\Factory::create();

        for ( $i=0; $i<10; $i++ )
        {
            User::create(
                [
                'name' => $faker->name, 
                'email' => $faker->email,
                'password' => 'password']
            );
        }
    }

}
