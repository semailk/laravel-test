<?php

namespace Database\Seeders;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@mail.ru',
            'password' => Hash::make('adminadmin')
        ]);

        $users = User::factory(10)->create();

        foreach ($users as $user){
            Friendship::create([
                'user_id' => $admin->id,
                'friend_id' => $user->id,
                'status' => 'pending',
            ]);
        }
    }
}
