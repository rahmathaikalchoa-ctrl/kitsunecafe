<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Animal;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $food = Category::create(['name' => 'Food']);
        $drinks = Category::create(['name' => 'Drinks']);
        $desserts = Category::create(['name' => 'Desserts']);
        $snacks = Category::create(['name' => 'Snacks']);

        $menuItems = [
            [$food->id,     'Kitsune Ramen',     'Rich tonkotsu broth with fox-shaped fishcake, soft-boiled egg, and green onion.',         35000, 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?auto=format&fit=crop&w=800&q=80'],
            [$food->id,     'Fox Den Toast',      'Thick milk bread topped with matcha cream and red bean paste, served warm.',               22000, 'https://images.unsplash.com/photo-1484723091739-30a097e8f929?auto=format&fit=crop&w=800&q=80'],
            [$food->id,     'Tamago Sandwich',    'Fluffy Japanese egg salad on soft shokupan bread.',                                        18000, 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=800&q=80'],
            [$drinks->id,   'Sakura Latte',       'Steamed milk with cherry blossom syrup and a dusting of pink powder.',                     28000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=800&q=80'],
            [$drinks->id,   'Matcha Fox',         'Ceremonial-grade matcha blended with oat milk, topped with fox latte art.',                32000, 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=800&q=80'],
            [$drinks->id,   'Yuzu Lemonade',      'Fresh yuzu citrus with sparkling water and honey, served over ice.',                       20000, 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?auto=format&fit=crop&w=800&q=80'],
            [$drinks->id,   'Hojicha Milk Tea',   'Roasted green tea with brown sugar boba and creamy milk.',                                 25000, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80'],
            [$desserts->id, 'Fox Waffle',         'Crispy waffle shaped like a fox face, served with maple syrup and whipped cream.',         30000, 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=800&q=80'],
            [$desserts->id, 'Mochi Ice Cream',    'Three pieces of seasonal mochi ice cream — strawberry, matcha, and mango.',                22000, 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=800&q=80'],
            [$desserts->id, 'Inari Cheesecake',   'Creamy no-bake cheesecake with a sesame cookie crust and tofu caramel.',                   28000, 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=800&q=80'],
            [$snacks->id,   'Edamame Bowl',       'Lightly salted edamame with a side of yuzu dipping sauce.',                                15000, 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80'],
            [$snacks->id,   'Fox Ears Chips',     'House-made taro chips shaped like fox ears, served with miso dip.',                        18000, 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?auto=format&fit=crop&w=800&q=80'],
            [$food->id,     'Kitsune Curry Rice', 'Japanese-style curry with tender vegetables over warm steamed rice.',                      30000, 'https://images.unsplash.com/photo-1604329760661-e71dc83f8f26?auto=format&fit=crop&w=800&q=80'],
            [$food->id,     'Sakura Soba',        'Chilled buckwheat noodles served with a savoury dipping broth and scallions.',             28000, 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?auto=format&fit=crop&w=800&q=80'],
            [$desserts->id, 'Fox Tail Pudding',   'Silky Japanese-style custard pudding topped with bittersweet caramel.',                    20000, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=800&q=80'],
            [$drinks->id,   'Kitsune Caramel Macchiato', 'Espresso layered with steamed milk and a ribbon of caramel.',                       30000, 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=800&q=80'],
            [$food->id,     'Inari Sushi Set',    'Sweet tofu pockets filled with seasoned sushi rice — a true Kitsune favourite.',           32000, 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=800&q=80'],
            [$desserts->id, 'Sakura Dorayaki',    'Fluffy pancakes filled with sweet red bean paste and a hint of cherry blossom.',           21000, 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?auto=format&fit=crop&w=800&q=80'],
            [$drinks->id,   'Kitsune Hot Cocoa',  'Rich dark hot chocolate topped with fox-shaped marshmallows.',                             26000, 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80'],
            [$snacks->id,   'Fox Paw Dango',      'Grilled mochi dango skewers glazed with sweet soy sauce.',                                 17000, 'https://images.unsplash.com/photo-1631709497146-a239ef373cf1?auto=format&fit=crop&w=800&q=80'],
        ];

        foreach ($menuItems as [$categoryId, $name, $description, $priceCents, $imagePath]) {
            MenuItem::create([
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'price_cents' => $priceCents,
                'is_available' => true,
                'image_path' => $imagePath,
            ]);
        }

        $foxes = [
            ['Kiku', 'Kiku is a gentle red fox who loves napping near the window in the afternoon sun. She was rescued as a kit and has lived at the cafe since it opened. Her favourite treat is a piece of our Fox Ears Chips.', 'https://images.unsplash.com/photo-1474511320723-9a56873867b5?auto=format&fit=crop&w=800&q=80'],
            ['Taiyo', 'Taiyo is the most energetic fox at the cafe. He greets every guest at the door with a curious sniff and a wagging tail. He is easily recognized by the white tip on his tail and his bright amber eyes.', 'https://images.unsplash.com/photo-1456926631375-92c8ce872def?auto=format&fit=crop&w=800&q=80'],
            ['Hoshi', 'Hoshi is a silver fox with a calm, regal temperament. She is often found sitting on the highest shelf in the reading corner, surveying the cafe with quiet dignity. Guests say she looks like she is running the place.', 'https://images.unsplash.com/photo-1517825738774-7de9363ef735?auto=format&fit=crop&w=800&q=80'],
            ['Mochi', 'Mochi is a small, fluffy arctic fox whose coat changes from pure white in winter to sandy brown in summer. He is shy at first but warms up quickly over a bowl of edamame.', 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=800&q=80'],
            ['Sora', 'Sora arrived at the cafe last spring and is still learning the ropes. She is playful and curious, often stealing guests\' napkins as a game. Staff say she is the troublemaker of the group, in the best possible way.', 'https://images.unsplash.com/photo-1611689342806-0863700ce1e4?auto=format&fit=crop&w=800&q=80'],
            ['Ren', 'Ren is the eldest fox at Kitsune Animal Cafe and is considered the guardian of the space. He moves slowly and deliberately, and a visit from Ren — who chooses his guests carefully — is said to bring good luck.', 'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?auto=format&fit=crop&w=800&q=80'],
        ];

        foreach ($foxes as [$name, $description, $imagePath]) {
            Animal::create([
                'name' => $name,
                'species' => 'fox',
                'image_path' => $imagePath,
                'description' => $description,
                'is_active' => true,
            ]);
        }

        // Demo reviewers (separate from the test user so reviews look natural)
        $reviewers = User::factory(6)->create();

        $allItems = MenuItem::all();

        // Give each reviewer a completed order so they can leave reviews
        foreach ($reviewers as $reviewer) {
            $sampledItems = $allItems->random(3);
            $total = $sampledItems->sum('price_cents');

            $order = Order::create([
                'user_id' => $reviewer->id,
                'status' => OrderStatus::Completed,
                'total_cents' => $total,
            ]);

            foreach ($sampledItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'quantity' => 1,
                    'price_cents' => $item->price_cents,
                ]);
            }
        }

        // Seed reviews — each reviewer reviews items they ordered
        $comments = [
            'Really enjoyed this, would order again!',
            'Great flavour, perfect portion size.',
            'Loved the presentation and the taste matched the description perfectly.',
            'A bit on the sweet side for my taste but still very good.',
            'One of the best things I have tried here.',
            'The foxes came over while I was eating this — 10/10 experience.',
            'Solid choice, nothing fancy but very satisfying.',
            'My new favourite item on the menu.',
            null,
            null,
        ];

        foreach ($reviewers as $reviewer) {
            $orderedItemIds = OrderItem::whereHas('order', fn ($q) => $q->where('user_id', $reviewer->id))
                ->pluck('menu_item_id');

            foreach ($orderedItemIds as $itemId) {
                Review::create([
                    'user_id' => $reviewer->id,
                    'menu_item_id' => $itemId,
                    'rating' => fake()->numberBetween(3, 5),
                    'comment' => fake()->randomElement($comments),
                ]);
            }
        }
    }
}
