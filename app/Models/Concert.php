<?php

namespace App\Models;

use App\Enums\ConcertSource;
use App\Enums\ConcertStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    /** @use HasFactory<\Database\Factories\ConcertFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'year',
        'type',
        'source',
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'source' => ConcertSource::class,
        'status' => ConcertStatus::class,
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'concert_occurrences')
            ->withPivot('date')
            ->withTimestamps();
    }

    public function occurrences()
    {
        return $this->hasMany(ConcertOccurrence::class);
    }
}
