<?php

namespace Database\Seeders;

use App\Models\Monster;
use Illuminate\Database\Seeder;

class MonsterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monsters = [
            // Easy Monsters
            [
                'name' => 'Shadow Imp',
                'hp' => 50,
                'attack' => 8,
                'defense' => 3,
                'difficulty' => 'easy',
                'description' => 'A small mischievous creature that lurks in the shadows. Perfect for beginners.',
                'image_path' => '/images/monsters/shadow-imp.png',
                'is_active' => true,
            ],
            [
                'name' => 'Forest Sprite',
                'hp' => 45,
                'attack' => 6,
                'defense' => 4,
                'difficulty' => 'easy',
                'description' => 'A playful forest creature with nature magic.',
                'image_path' => '/images/monsters/forest-sprite.png',
                'is_active' => true,
            ],
            [
                'name' => 'Rock Golem',
                'hp' => 60,
                'attack' => 5,
                'defense' => 8,
                'difficulty' => 'easy',
                'description' => 'A slow but sturdy creature made of stone.',
                'image_path' => '/images/monsters/rock-golem.png',
                'is_active' => true,
            ],

            // Medium Monsters
            [
                'name' => 'Fire Drake',
                'hp' => 80,
                'attack' => 12,
                'defense' => 6,
                'difficulty' => 'medium',
                'description' => 'A fierce dragon-like creature that breathes fire.',
                'image_path' => '/images/monsters/fire-drake.png',
                'is_active' => true,
            ],
            [
                'name' => 'Ice Wolf',
                'hp' => 75,
                'attack' => 10,
                'defense' => 7,
                'difficulty' => 'medium',
                'description' => 'A wolf from the frozen tundra with ice-cold fangs.',
                'image_path' => '/images/monsters/ice-wolf.png',
                'is_active' => true,
            ],
            [
                'name' => 'Thunder Bird',
                'hp' => 70,
                'attack' => 14,
                'defense' => 5,
                'difficulty' => 'medium',
                'description' => 'A majestic bird that controls lightning and storms.',
                'image_path' => '/images/monsters/thunder-bird.png',
                'is_active' => true,
            ],

            // Hard Monsters
            [
                'name' => 'Ancient Dragon',
                'hp' => 120,
                'attack' => 18,
                'defense' => 12,
                'difficulty' => 'hard',
                'description' => 'An ancient and powerful dragon with centuries of wisdom.',
                'image_path' => '/images/monsters/ancient-dragon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Shadow Lord',
                'hp' => 100,
                'attack' => 20,
                'defense' => 10,
                'difficulty' => 'hard',
                'description' => 'A master of dark magic and shadows.',
                'image_path' => '/images/monsters/shadow-lord.png',
                'is_active' => true,
            ],
            [
                'name' => 'Crystal Guardian',
                'hp' => 110,
                'attack' => 16,
                'defense' => 15,
                'difficulty' => 'hard',
                'description' => 'A powerful guardian made of enchanted crystals.',
                'image_path' => '/images/monsters/crystal-guardian.png',
                'is_active' => true,
            ],
        ];

        foreach ($monsters as $monsterData) {
            Monster::create($monsterData);
        }
    }
}
