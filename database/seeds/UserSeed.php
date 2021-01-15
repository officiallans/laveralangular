<?php

use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{

    public function run()
    {
        $users = array(
            ['name' => 'TEST user', 'email' => 'test@gmail.com', 'password' => 'secret', 'type' => 'participant'],
            ['name' => 'TEST manager', 'email' => 'manager@gmail.com', 'password' => 'secret', 'type' => 'manager'],
        );

        // Loop through each user above and create the record for them in the database
        foreach ($users as $user) {
            App\User::create($user);
        }

    }
}
