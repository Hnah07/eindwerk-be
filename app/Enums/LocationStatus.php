<?php

namespace App\Enums;

enum LocationStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
}
