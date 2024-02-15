<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create(['email' => 'admin@gmail.com', 'role' => true]);
        Zone::factory()->create(['nom' => 'Afrique']);
        Zone::factory()->create(['nom' => 'Europe']);
        Zone::factory()->create(['nom' => 'Asie']);
        User::factory(30)->create();
        Category::factory(20)->create();
        Client::factory(20)->create();
        Product::factory(20)->hasImages(3)->create();
        // Transport::factory(20)->hasZones(3)->create();
        Order::factory(20)->hasAttached(
            Product::factory(10)->hasImages(3)->count(4),
            ['montant' => rand(50000, 100000), 'quantity' => rand(1, 10)]
        )->create();
    }
}
