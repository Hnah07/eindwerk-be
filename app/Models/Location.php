<?php

namespace App\Models;

use App\Enums\LocationSource;
use App\Enums\LocationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source',
        'status',
        'longitude',
        'latitude',
        'street',
        'housenr',
        'zipcode',
        'city',
        'website',
        'country_id'
    ];

    protected $casts = [
        'source' => LocationSource::class,
        'status' => LocationStatus::class,
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
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
