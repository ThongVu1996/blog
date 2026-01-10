<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::truncate();
        Category::truncate();
        User::create([
            'email' => 'anhthongvu1996@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Vuthong1996a@'),
        ]);
        /* $categories = [ */
        /*     ['id' => 1, 'name' => 'Docker'], */
        /* ]; */

        /* foreach ($categories as $cat) { */
        /*     Category::updateOrCreate(['id' => $cat['id']], $cat); */
        /* } */
    }
}
