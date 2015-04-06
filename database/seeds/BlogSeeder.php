<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Fooblog\User;
use Fooblog\Blog;

class BlogSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $faker = Faker\Factory::create();
        
        for ( $i=0; $i<1000; $i++ ) 
        {
            $published = $faker->dateTimeBetween('-1 year', 'now');

            Blog::create(
                [
                'user_id' => User::query()->random()->first()->id, 
                'text' => $faker->text(1000),
                'created_at' => $published,
                'updated_at' => $published ]
            );
        }
    }

}
