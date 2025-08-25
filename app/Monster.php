<?php

namespace App;


class Monster
{
    public int $id;
    public string $name;
    public int $hp;
    public int $attack;
    public int $defense;
    public string $image_path;
    public string $difficulty;

    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'];
        $this->name = $attributes['name'];
        $this->hp = $attributes['hp'];
        $this->attack = $attributes['attack'];
        $this->defense = $attributes['defense'];
        $this->image_path = $attributes['image_path'];
        $this->difficulty = $attributes['difficulty'];
    }

    public static function find(int $id): ?self
    {
        $allMonsters = self::all();
        return $allMonsters->firstWhere('id', $id);
    }

    public static function all(): \Illuminate\Support\Collection
    {
        $monsters = collect();
        foreach (config('monsters') as $difficultyGroup) {
            foreach ($difficultyGroup as $monster) {
                $monsters->push(new self($monster));
            }
        }
        return $monsters;
    }

    public static function getByDifficulty(string $difficulty): \Illuminate\Support\Collection
    {
        return collect(config("monsters.{$difficulty}", []))
            ->map(fn($monster) => new self($monster));
    }

    public static function getRandom(string $difficulty = null): \Illuminate\Support\Collection
    {
        if ($difficulty) {
            $monsters = self::getByDifficulty($difficulty);
        } else {
            $monsters = self::all();
        }

//        if ($monsters->isEmpty()) {
//            return null;
//        }
        return $monsters->random();
    }
}
