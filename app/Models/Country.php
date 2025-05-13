<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code'];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
