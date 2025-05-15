<?php

namespace App\Enums;

enum ConcertStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
}
