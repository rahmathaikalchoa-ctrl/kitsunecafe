<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Category;
use App\Models\MenuItem;
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
            [$food->id, 'Kitsune Ramen', 'Rich tonkotsu broth with fox-shaped fishcake, soft-boiled egg, and green onion.', 2800],
            [$food->id, 'Fox Den Toast', 'Thick milk bread topped with matcha cream and red bean paste, served warm.', 1800],
            [$food->id, 'Tamago Sandwich', 'Fluffy Japanese egg salad on soft shokupan bread.', 1500],
            [$drinks->id, 'Sakura Latte', 'Steamed milk with cherry blossom syrup and a dusting of pink powder.', 1200],
            [$drinks->id, 'Matcha Fox', 'Ceremonial-grade matcha blended with oat milk, topped with fox latte art.', 1400],
            [$drinks->id, 'Yuzu Lemonade', 'Fresh yuzu citrus with sparkling water and honey, served over ice.', 900],
            [$drinks->id, 'Hojicha Milk Tea', 'Roasted green tea with brown sugar boba and creamy milk.', 1100],
            [$desserts->id, 'Fox Waffle', 'Crispy waffle shaped like a fox face, served with maple syrup and whipped cream.', 1600],
            [$desserts->id, 'Mochi Ice Cream', 'Three pieces of seasonal mochi ice cream — strawberry, matcha, and mango.', 1300],
            [$desserts->id, 'Inari Cheesecake', 'Creamy no-bake cheesecake with a sesame cookie crust and tofu caramel.', 1500],
            [$snacks->id, 'Edamame Bowl', 'Lightly salted edamame with a side of yuzu dipping sauce.', 700],
            [$snacks->id, 'Fox Ears Chips', 'House-made taro chips shaped like fox ears, served with miso dip.', 800],
        ];

        foreach ($menuItems as [$categoryId, $name, $description, $priceCents]) {
            MenuItem::create([
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'price_cents' => $priceCents,
                'is_available' => true,
            ]);
        }

        $foxes = [
            ['Kiku', 'Kiku is a gentle red fox who loves napping near the window in the afternoon sun. She was rescued as a kit and has lived at the cafe since it opened. Her favourite treat is a piece of our Fox Ears Chips.'],
            ['Taiyo', 'Taiyo is the most energetic fox at the cafe. He greets every guest at the door with a curious sniff and a wagging tail. He is easily recognized by the white tip on his tail and his bright amber eyes.'],
            ['Hoshi', 'Hoshi is a silver fox with a calm, regal temperament. She is often found sitting on the highest shelf in the reading corner, surveying the cafe with quiet dignity. Guests say she looks like she is running the place.'],
            ['Mochi', 'Mochi is a small, fluffy arctic fox whose coat changes from pure white in winter to sandy brown in summer. He is shy at first but warms up quickly over a bowl of edamame.'],
            ['Sora', 'Sora arrived at the cafe last spring and is still learning the ropes. She is playful and curious, often stealing guests\' napkins as a game. Staff say she is the troublemaker of the group, in the best possible way.'],
            ['Ren', 'Ren is the eldest fox at Kitsune Animal Cafe and is considered the guardian of the space. He moves slowly and deliberately, and a visit from Ren — who chooses his guests carefully — is said to bring good luck.'],
        ];

        foreach ($foxes as [$name, $description]) {
            Animal::create([
                'name' => $name,
                'species' => 'fox',
                'description' => $description,
                'is_active' => true,
            ]);
        }
    }
}
