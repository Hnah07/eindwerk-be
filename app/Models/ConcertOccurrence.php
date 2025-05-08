<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcertOccurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'concert_id',
        'location_id',
        'date'
    ];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
