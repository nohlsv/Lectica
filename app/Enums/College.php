<?php

namespace App\Enums;

enum College: string
{
    case COLLEGE_OF_ENGINEERING_AND_ARCHITECTURE = 'College of Engineering and Architecture';
    case COLLEGE_OF_ALLIED_HEALTH_AND_SCIENCES = 'College of Allied Health and Sciences';
    case COLLEGE_OF_BUSINESS_AND_ACCOUNTANCY = 'College of Business and Accountancy';
    case COLLEGE_OF_ARTS_AND_SCIENCE = 'College of Arts and Science';
    case COLLEGE_OF_COMPUTER_STUDIES = 'College of Computer Studies';
    case COLLEGE_OF_TECHNOLOGY = 'College of Technology';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::COLLEGE_OF_ENGINEERING_AND_ARCHITECTURE->value => 'College of Engineering and Architecture',
            self::COLLEGE_OF_ALLIED_HEALTH_AND_SCIENCES->value => 'College of Allied Health and Sciences',
            self::COLLEGE_OF_BUSINESS_AND_ACCOUNTANCY->value => 'College of Business and Accountancy',
            self::COLLEGE_OF_ARTS_AND_SCIENCE->value => 'College of Arts and Science',
            self::COLLEGE_OF_COMPUTER_STUDIES->value => 'College of Computer Studies',
            self::COLLEGE_OF_TECHNOLOGY->value => 'College of Technology',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}