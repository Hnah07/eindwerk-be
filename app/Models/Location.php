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
        'country_id'
    ];

    public function concerts()
    {
        return $this->belongsToMany(Concert::class, 'concert_occurrences')
            ->withPivot('date')
            ->withTimestamps();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
