<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Slide;
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
        User::factory()->create(['email' => 'admin@gmail.com', 'role' => RoleEnum::ADMIN->value]);
        Slide::factory()->create([
            'text_one' => 'Supper value deals',
            'text_two' => 'On all products',
            'paragraph' => 'Save more with coupons & up to 70% off',
            'image' => '/assets/imgs/slider/slider-1.png',
        ]);
        Slide::factory()->create([
            'text_one' => 'Fashion Trending',
            'text_two' => 'Great Collection',
            'paragraph' => 'Save more with coupons & up to 20% off',
            'image' => '/assets/imgs/slider/slider-1.png',
        ]);
        Slide::factory()->create([
            'text_one' => 'Big Deals From',
            'text_two' => 'Manufacturer',
            'paragraph' => 'Clothing, Shoes, Bags, Wallets...',
            'image' => '/assets/imgs/slider/slider-1.png',
        ]);
        Zone::factory()->hasCountries(5)->create(['nom' => 'Afrique']);
        Zone::factory()->hasCountries(5)->create(['nom' => 'Europe']);
        Zone::factory()->hasCountries(5)->create(['nom' => 'Asie']);
        Transport::factory()->hasZones(2)->create(['nom' => 'DHL']);
        Transport::factory()->hasZones(2)->create(['nom' => 'FeDEX']);
        Poids::factory()->create(['min' => '1', 'max' => '2']);
        Poids::factory()->create(['min' => '3', 'max' => '4']);
        Poids::factory()->create(['min' => '5', 'max' => '7']);
        Poids::factory()->create(['min' => '8', 'max' => '10']);
        Shipping::factory(20)->create();
        User::factory(30)->create();
        Category::factory(20)->create();
        Client::factory(30)->create();
        Product::factory(30)->hasImages(3)->create();
        Order::factory(30)->hasAttached(
            Product::factory(10)->hasImages(3)->count(4),
            ['montant' => rand(50000, 100000), 'quantity' => rand(1, 10)]
        )->create();
    }
}
