<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Client;
use App\Models\Devise;
use App\Models\Order;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Shipping;
use App\Models\Slide;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create(['email' => 'admin@gmail.com', 'role' => RoleEnum::ADMIN->value]);
        // Devise::factory()->create(['type' => 'USD', 'taux' => 654]);
        Devise::factory()->create(['type' => 'EUR', 'taux' => 656]);
        Slide::factory()->create([
            'text_one' => 'Supper value deals',
            'text_two' => 'On all products',
            'paragraph' => 'Save more with coupons & up to 70% off',
            'image' => 'slide/image/slider-1.png',
        ]);
        Slide::factory()->create([
            'text_one' => 'Fashion Trending',
            'text_two' => 'Great Collection',
            'paragraph' => 'Save more with coupons & up to 20% off',
            'image' => 'slide/image/slider-2.png',
        ]);
        Slide::factory()->create([
            'text_one' => 'Big Deals From',
            'text_two' => 'Manufacturer',
            'paragraph' => 'Clothing, Shoes, Bags, Wallets...',
            'image' => 'slide/image/slider-3.png',
        ]);
        $zone = Zone::factory()->hasCountries(5)->create(['nom' => 'Afrique']);
        // Zone::factory()->hasCountries(5)->create(['nom' => 'Europe']);
        // Zone::factory()->hasCountries(5)->create(['nom' => 'Asie']);
        $transports = collect([
            Transport::factory()->create(['nom' => 'DHL']),
            Transport::factory()->create(['nom' => 'FeDEX']),
        ]);
        $zone->transports()->attach($transports->pluck('id'));
        $poids = collect([
            Poids::factory()->create(['min' => '1', 'max' => '2']),
            Poids::factory()->create(['min' => '3', 'max' => '4']),
        ]);
        // Poids::factory()->create(['min' => '5', 'max' => '7']);
        // Poids::factory()->create(['min' => '8', 'max' => '10']);
        // Un tarif par transporteur et par tranche de poids pour la zone des pays.
        foreach ($transports as $transport) {
            foreach ($poids as $range) {
                Shipping::factory()->create([
                    'zone_id' => $zone->id,
                    'transport_id' => $transport->id,
                    'poids_id' => $range->id,
                ]);
            }
        }
        User::factory(10)->create();
        Category::factory(5)->create();
        Client::factory(30)->create();
        Product::factory(30)->hasImages(3)->create();
        // Promotion::factory(5)->hasProducts(10)->create();
        // Order::factory(30)->hasAttached(
        //     Product::factory(10)->hasImages(3)->count(4),
        //     ['montant' => rand(50000, 100000), 'quantity' => rand(1, 10)]
        // )->create();
    }
}
