<?php

namespace App\Enums;

enum QuizType: string
{
    case ENUMERATION = 'enumeration';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::ENUMERATION->value => 'Enumeration',
            self::MULTIPLE_CHOICE->value => 'Multiple Choice',
            self::TRUE_FALSE->value => 'True or False',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
