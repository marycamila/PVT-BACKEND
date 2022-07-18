<?php

namespace App\Models;

use App\Models\Admin\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherType extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    public $fillable = ['name','module_id'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

}
