<?php

namespace App\Enums;

enum College: string
{
    case COLLEGE_OF_COMPUTER_STUDIES = 'College of Computer Studies';
    case COLLEGE_OF_ENGINEERING = 'College of Engineering';
    case COLLEGE_OF_BUSINESS = 'College of Business';
    case COLLEGE_OF_TECHNOLOGY = 'College of Technology';
    case COLLEGE_OF_ALLIED_HEALTH_AND_SCIENCES = 'College of Allied Health and Sciences';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::COLLEGE_OF_COMPUTER_STUDIES->value => 'College of Computer Studies',
            self::COLLEGE_OF_ENGINEERING->value => 'College of Engineering',
            self::COLLEGE_OF_BUSINESS->value => 'College of Business',
            self::COLLEGE_OF_TECHNOLOGY->value => 'College of Technology',
            self::COLLEGE_OF_ALLIED_HEALTH_AND_SCIENCES->value => 'College of Allied Health and Sciences',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}