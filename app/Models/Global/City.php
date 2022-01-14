<?php

namespace App\Models\Global;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;
    public $fillable = ['name', 'first_shortened', 'second_shortened', 'third_shortened', 'latitude', 'longitude'];

    public function getLatitudeAttribute($value)
    {
        return floatval($value);
    }

    public function getLongitudeAttribute($value)
    {
        return floatval($value);
    }
    public function users()
    {
    return $this->hasMany(User::class);
    }
}
