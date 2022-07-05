<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class Address extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = "addresses";
    public $guarded = ['id'];
    protected $fillable = [
        'city_address_id',
        'zone',
        'street',
        'number_address',
        'created_at',
        'updated_at',
        'latitude',
        'longitude',
        'description'
    ];
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
    public function affiliate()
    {
    	return $this->morphTo(Affiliate::class);
    }
}
