<?php

namespace App\Enums;

enum LocationSource: string
{
    case MANUAL = 'manual';
    case API = 'api';
}
