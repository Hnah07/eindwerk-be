<?php

namespace App\Enums;

enum ConcertSource: string
{
    case MANUAL = 'manual';
    case API = 'api';
}
