<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;
use App\Models\Affiliate;

use Util;

class Address extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = "addresses";
    public $guarded = [];

    protected $attributes = array(
        'city_address_id' => null,
        'zone' => null,
        'street' => null,
        'number_address' => null,
        'description' => null
    );
    public function city()
    {
        return $this->belongsTo(City::class,'city_address_id','id');
    }
    public function cityName()
    {
        return $this->city->name;
    }
    public function affiliate()
    {
    	return $this->belongsToMany(Affiliate::class);
    }
}
