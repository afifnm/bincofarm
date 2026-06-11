<?php

declare(strict_types=1);

namespace App\Enums;

enum GradeHasil: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';

    public function label(): string
    {
        return 'Grade ' . $this->value;
    }
}
