<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'longitude',
        'latitude',
        'street',
        'housenr',
        'zipcode',
        'city',
        'website',
        'country'
    ];

    public function concerts()
    {
        return $this->belongsToMany(Concert::class, 'concert_occurrences')
            ->withPivot('date')
            ->withTimestamps();
    }
}
