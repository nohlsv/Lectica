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

            [
                'name' => 'Triangle Head',
                'hp' => 3030,
                'attack' => 1000,
                'defense' => 500,
                'image_path' => '/images/monsters/TriangleHead.png',
                'difficulty' => 'easy',
                'is_active' => true,
            ],
            [
                'name' => 'Fiery Slime',
                'hp' => 1234,
                'attack' => 567,
                'defense' => 910,
                'image_path' => '/images/monsters/FierySlime.png',
                'difficulty' => 'easy',
                'is_active' => true,
            ],
            [
                'name' => 'Green Goblin',
                'hp' => 2460,
                'attack' => 1870,
                'defense' => 2000,
                'image_path' => '/images/monsters/GreenGoblin.png',
                'difficulty' => 'easy',
                'is_active' => true,
            ],
            [
                'name' => 'Clippy',
                'hp' => 650,
                'attack' => 973,
                'defense' => 1998,
                'image_path' => '/images/monsters/Clippy.png',
                'difficulty' => 'easy',
                'is_active' => true,
            ],
            [
                'name' => 'Pixel Cultist',
                'hp' => 210,
                'attack' => 100,
                'defense' => 500,
                'image_path' => '/images/monsters/PixelCultist.png',
                'difficulty' => 'easy',
                'is_active' => true,
            ],
            [
                'name' => 'Happy Balloon',
                'hp' => 10,
                'attack' => 9999,
                'defense' => 10,
                'image_path' => '/images/monsters/HappyBalloon.png',
                'difficulty' => 'easy',
                'is_active' => true,
            ],
            [
                'name' => 'Jujutsu Dog',
                'hp' => 3000,
                'attack' => 101,
                'defense' => 102,
                'image_path' => '/images/monsters/JujutsuDog.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Triangle Genie',
                'hp' => 1111,
                'attack' => 2222,
                'defense' => 3333,
                'image_path' => '/images/monsters/TriangleGenie.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Herbivorous Hammer',
                'hp' => 1987,
                'attack' => 2000,
                'defense' => 2025,
                'image_path' => '/images/monsters/HerbivorousHammer.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Car Big Arms',
                'hp' => 6000,
                'attack' => 7000,
                'defense' => 100,
                'image_path' => '/images/monsters/CarBigArms.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Chef Capybara',
                'hp' => 5555,
                'attack' => 10,
                'defense' => 5555,
                'image_path' => '/images/monsters/ChefCapybara.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Penguin Knife',
                'hp' => 70,
                'attack' => 3777,
                'defense' => 7,
                'image_path' => '/images/monsters/PenguinKnife.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Not A Mimic',
                'hp' => 99,
                'attack' => 4999,
                'defense' => 99,
                'image_path' => '/images/monsters/NotAMimic.png',
                'difficulty' => 'medium',
                'is_active' => true,
            ],
            [
                'name' => 'Job The Reality Checker',
                'hp' => 10,
                'attack' => 9999,
                'defense' => 9999,
                'image_path' => '/images/monsters/JobTheRealityChecker.png',
                'difficulty' => 'hard',
                'is_active' => true,
            ],
            [
                'name' => 'Yggdrasils Cousin',
                'hp' => 1337,
                'attack' => 50,
                'defense' => 7331,
                'image_path' => '/images/monsters/YggdrasilsCousin.png',
                'difficulty' => 'hard',
                'is_active' => true,
            ],
            [
                'name' => 'Billy The Aquatic Vertebrate',
                'hp' => 5432,
                'attack' => 876,
                'defense' => 9,
                'image_path' => '/images/monsters/BillyTheAquaticVertebrate.png',
                'difficulty' => 'hard',
                'is_active' => true,
            ],
            [
                'name' => 'Caloric No Deficit',
                'hp' => 250,
                'attack' => 5000,
                'defense' => 50,
                'image_path' => '/images/monsters/CaloricNoDeficit.png',
                'difficulty' => 'hard',
                'is_active' => true,
            ],
            [
                'name' => 'Insomnia Inducer',
                'hp' => 1000,
                'attack' => 1206,
                'defense' => 499,
                'image_path' => '/images/monsters/InsomniaInducer.png',
                'difficulty' => 'hard',
                'is_active' => true,
            ],
            [
                'name' => 'Viru Sahastrabudhhe',
                'hp' => 404,
                'attack' => 404,
                'defense' => 4040,
                'image_path' => '/images/monsters/ViruSahastrabudhhe.png',
                'difficulty' => 'hard',
                'is_active' => true,
            ],
        ];

        foreach ($monsters as $monsterData) {
            Monster::create($monsterData);
        }
    }
}
