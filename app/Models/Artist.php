<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = ['name', 'description', 'country_id', 'image_url'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
