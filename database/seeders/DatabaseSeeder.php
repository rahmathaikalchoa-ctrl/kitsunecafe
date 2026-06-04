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
            [
                'name' => 'Kiku', 'gender' => 'Female', 'age' => 4, 'color' => 'Red', 'arrived_year' => 2021,
                'description' => 'Kiku is a gentle red fox who loves napping near the window in the afternoon sun. She was rescued as a kit and has lived at the cafe since it opened. Her favourite treat is a piece of our Fox Ears Chips.',
                'personality' => ['Gentle', 'Sleepy', 'Affectionate'],
                'favourite_treat' => 'Fox Ears Chips', 'favourite_spot' => 'The sunny window seat',
                'fun_facts' => ['Was rescued as a tiny kit', 'Can sleep up to 14 hours a day', 'Chirps softly when she is happy'],
                'image_path' => 'kiku.jpg',
            ],
            [
                'name' => 'Taiyo', 'gender' => 'Male', 'age' => 3, 'color' => 'Red', 'arrived_year' => 2022,
                'description' => 'Taiyo is the most energetic fox at the cafe. He greets every guest at the door with a curious sniff and a wagging tail. He is easily recognized by the white tip on his tail and his bright amber eyes.',
                'personality' => ['Energetic', 'Friendly', 'Curious'],
                'favourite_treat' => 'Tamago Sandwich', 'favourite_spot' => 'By the front door',
                'fun_facts' => ['Greets nearly every guest at the door', 'Recognized by his white tail tip', 'Can leap over a metre high'],
                'image_path' => 'taiyo.jpg',
            ],
            [
                'name' => 'Hoshi', 'gender' => 'Female', 'age' => 5, 'color' => 'Silver', 'arrived_year' => 2020,
                'description' => 'Hoshi is a silver fox with a calm, regal temperament. She is often found sitting on the highest shelf in the reading corner, surveying the cafe with quiet dignity. Guests say she looks like she is running the place.',
                'personality' => ['Calm', 'Regal', 'Observant'],
                'favourite_treat' => 'Inari Cheesecake', 'favourite_spot' => 'The top reading-corner shelf',
                'fun_facts' => ['Prefers the highest perch in the room', 'Rarely makes a sound', 'Looks like she runs the place'],
                'image_path' => 'hoshi.jpg',
            ],
            [
                'name' => 'Mochi', 'gender' => 'Male', 'age' => 2, 'color' => 'Arctic', 'arrived_year' => 2023,
                'description' => 'Mochi is a small, fluffy arctic fox whose coat changes from pure white in winter to sandy brown in summer. He is shy at first but warms up quickly over a bowl of edamame.',
                'personality' => ['Shy', 'Fluffy', 'Sweet'],
                'favourite_treat' => 'Edamame Bowl', 'favourite_spot' => 'The cool tiles near the kitchen',
                'fun_facts' => ['His coat turns pure white in winter', 'Shy at first but quick to warm up', 'The smallest fox at the cafe'],
                'image_path' => 'mochi.jpg',
            ],
            [
                'name' => 'Sora', 'gender' => 'Female', 'age' => 1, 'color' => 'Red', 'arrived_year' => 2024,
                'description' => 'Sora arrived at the cafe last spring and is still learning the ropes. She is playful and curious, often stealing guests\' napkins as a game. Staff say she is the troublemaker of the group, in the best possible way.',
                'personality' => ['Playful', 'Mischievous', 'Curious'],
                'favourite_treat' => 'Fox Waffle', 'favourite_spot' => 'Under the corner tables',
                'fun_facts' => ['Loves stealing napkins as a game', 'The newest member of the group', 'Has seemingly endless curiosity'],
                'image_path' => 'sora.jpg',
            ],
            [
                'name' => 'Ren', 'gender' => 'Male', 'age' => 8, 'color' => 'Red', 'arrived_year' => 2019,
                'description' => 'Ren is the eldest fox at Kitsune Animal Cafe and is considered the guardian of the space. He moves slowly and deliberately, and a visit from Ren — who chooses his guests carefully — is said to bring good luck.',
                'personality' => ['Wise', 'Calm', 'Dignified'],
                'favourite_treat' => 'Kitsune Curry Rice', 'favourite_spot' => 'The warm hearth by the entrance',
                'fun_facts' => ['The eldest fox at the cafe', 'A visit from Ren is said to bring good luck', 'Chooses his guests carefully'],
                'image_path' => 'ren.jpg',
            ],
            [
                'name' => 'Yuki', 'gender' => 'Female', 'age' => 3, 'color' => 'Arctic', 'arrived_year' => 2022,
                'description' => 'Yuki is a snow-white fox with the softest coat at the cafe. She is calm and a little aloof, preferring quiet corners and the cool tile by the entrance. Regulars say catching Yuki in a playful mood is a rare treat.',
                'personality' => ['Calm', 'Aloof', 'Elegant'],
                'favourite_treat' => 'Mochi Ice Cream', 'favourite_spot' => 'The cool tile by the entrance',
                'fun_facts' => ['Has the softest coat at the cafe', 'Enjoys quiet corners', 'A playful mood from Yuki is a rare treat'],
                'image_path' => 'yuki.jpg',
            ],
            [
                'name' => 'Haru', 'gender' => 'Male', 'age' => 2, 'color' => 'Red', 'arrived_year' => 2023,
                'description' => 'Haru is a warm-hearted fox who seems to glow in the morning light. He loves greeting the first guests of the day and dozing in sunbeams. His gentle eyes have made him a favourite among returning visitors.',
                'personality' => ['Warm', 'Gentle', 'Sociable'],
                'favourite_treat' => 'Fox Den Toast', 'favourite_spot' => 'The morning sunbeam by the east window',
                'fun_facts' => ['Loves greeting the first guests of the day', 'Seems to glow in morning light', 'A favourite of returning visitors'],
                'image_path' => 'haru.jpg',
            ],
            [
                'name' => 'Kaze', 'gender' => 'Male', 'age' => 2, 'color' => 'Red', 'arrived_year' => 2024,
                'description' => 'Kaze is the fastest fox at the cafe, always darting between tables in a flash of orange. Energetic and mischievous, he turns every visit into a little adventure — keep an eye on your snacks when Kaze is around.',
                'personality' => ['Energetic', 'Mischievous', 'Quick'],
                'favourite_treat' => 'Fox Ears Chips', 'favourite_spot' => 'Wherever a snack is unattended',
                'fun_facts' => ['The fastest fox at the cafe', 'Turns every visit into an adventure', 'Keep an eye on your snacks'],
                'image_path' => 'kaze.jpg',
            ],
        ];

        foreach ($foxes as $fox) {
            Animal::create([
                ...$fox,
                'species' => 'fox',
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
