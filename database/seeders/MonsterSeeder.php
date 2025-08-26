<?php

namespace Database\Seeders;

use App\Models\Monster;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonsterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing monsters
        Monster::truncate();

        // Easy monsters
        $easyMonsters = [
            [
                'name' => 'Triangle Head',
                'hp' => 3030,
                'attack' => 1000,
                'defense' => 500,
                'image_path' => '/images/monsters/TriangleHead.png',
                'difficulty' => 'easy',
                'description' => 'A mysterious geometric entity with deadly precision.',
            ],
            [
                'name' => 'Fiery Slime',
                'hp' => 1234,
                'attack' => 567,
                'defense' => 910,
                'image_path' => '/images/monsters/FierySlime.png',
                'difficulty' => 'easy',
                'description' => 'A molten blob that burns with ancient knowledge.',
            ],
            [
                'name' => 'Green Goblin',
                'hp' => 2460,
                'attack' => 1870,
                'defense' => 2000,
                'image_path' => '/images/monsters/GreenGoblin.png',
                'difficulty' => 'easy',
                'description' => 'A cunning creature that tests your basic understanding.',
            ],
            [
                'name' => 'Clippy',
                'hp' => 650,
                'attack' => 973,
                'defense' => 1998,
                'image_path' => '/images/monsters/Clippy.png',
                'difficulty' => 'easy',
                'description' => 'An overly helpful assistant turned malevolent.',
            ],
            [
                'name' => 'Pixel Cultist',
                'hp' => 210,
                'attack' => 100,
                'defense' => 500,
                'image_path' => '/images/monsters/PixelCultist.png',
                'difficulty' => 'easy',
                'description' => 'A digital devotee of forbidden knowledge.',
            ],
            [
                'name' => 'Happy Balloon',
                'hp' => 10,
                'attack' => 9999,
                'defense' => 10,
                'image_path' => '/images/monsters/HappyBalloon.png',
                'difficulty' => 'easy',
                'description' => 'Deceptively cheerful with devastating attacks.',
            ],
        ];

        // Medium monsters
        $mediumMonsters = [
            [
                'name' => 'Jujutsu Dog',
                'hp' => 3000,
                'attack' => 101,
                'defense' => 102,
                'image_path' => '/images/monsters/JujutsuDog.png',
                'difficulty' => 'medium',
                'description' => 'A martial arts master in canine form.',
            ],
            [
                'name' => 'Triangle Genie',
                'hp' => 1111,
                'attack' => 2222,
                'defense' => 3333,
                'image_path' => '/images/monsters/TriangleGenie.png',
                'difficulty' => 'medium',
                'description' => 'Grants wishes in the most twisted ways.',
            ],
            [
                'name' => 'Herbivorous Hammer',
                'hp' => 1987,
                'attack' => 2000,
                'defense' => 2025,
                'image_path' => '/images/monsters/HerbivorousHammer.png',
                'difficulty' => 'medium',
                'description' => 'A peaceful tool turned weapon of war.',
            ],
            [
                'name' => 'Car Big Arms',
                'hp' => 6000,
                'attack' => 7000,
                'defense' => 100,
                'image_path' => '/images/monsters/CarBigArms.png',
                'difficulty' => 'medium',
                'description' => 'Automotive engineering gone terribly wrong.',
            ],
            [
                'name' => 'Chef Capybara',
                'hp' => 5555,
                'attack' => 10,
                'defense' => 5555,
                'image_path' => '/images/monsters/ChefCapybara.png',
                'difficulty' => 'medium',
                'description' => 'Cooks up trouble with zen-like patience.',
            ],
            [
                'name' => 'Penguin Knife',
                'hp' => 70,
                'attack' => 3777,
                'defense' => 7,
                'image_path' => '/images/monsters/PenguinKnife.png',
                'difficulty' => 'medium',
                'description' => 'Arctic assassin with deadly precision.',
            ],
            [
                'name' => 'Not A Mimic',
                'hp' => 99,
                'attack' => 4999,
                'defense' => 99,
                'image_path' => '/images/monsters/NotAMimic.png',
                'difficulty' => 'medium',
                'description' => 'Definitely not what it appears to be.',
            ],
        ];

        // Hard monsters
        $hardMonsters = [
            [
                'name' => 'Job The Reality Checker',
                'hp' => 10,
                'attack' => 9999,
                'defense' => 9999,
                'image_path' => '/images/monsters/JobTheRealityChecker.png',
                'difficulty' => 'hard',
                'description' => 'Tests your understanding of existence itself.',
            ],
            [
                'name' => 'Yggdrasils Cousin',
                'hp' => 1337,
                'attack' => 50,
                'defense' => 7331,
                'image_path' => '/images/monsters/YggdrasilsCousin.png',
                'difficulty' => 'hard',
                'description' => 'A lesser world tree with infinite knowledge.',
            ],
            [
                'name' => 'Billy The Aquatic Vertebrate',
                'hp' => 5432,
                'attack' => 876,
                'defense' => 9,
                'image_path' => '/images/monsters/BillyTheAquaticVertebrate.png',
                'difficulty' => 'hard',
                'description' => 'Swimming in the depths of wisdom.',
            ],
            [
                'name' => 'Caloric No Deficit',
                'hp' => 250,
                'attack' => 5000,
                'defense' => 50,
                'image_path' => '/images/monsters/CaloricNoDeficit.png',
                'difficulty' => 'hard',
                'description' => 'Consumes knowledge without restraint.',
            ],
            [
                'name' => 'Insomnia Inducer',
                'hp' => 1000,
                'attack' => 1206,
                'defense' => 499,
                'image_path' => '/images/monsters/InsomniaInducer.png',
                'difficulty' => 'hard',
                'description' => 'Keeps you awake with endless questions.',
            ],
            [
                'name' => 'Viru Sahastrabudhhe',
                'hp' => 404,
                'attack' => 404,
                'defense' => 4040,
                'image_path' => '/images/monsters/ViruSahastrabudhhe.png',
                'difficulty' => 'hard',
                'description' => 'The ultimate test of academic prowess.',
            ],
        ];

        // Insert all monsters
        foreach ([$easyMonsters, $mediumMonsters, $hardMonsters] as $monsters) {
            foreach ($monsters as $monster) {
                Monster::create($monster);
            }
        }

        $this->command->info('Monsters seeded successfully!');
    }
}
