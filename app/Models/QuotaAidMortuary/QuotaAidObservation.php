<?php

namespace App\Models\QuotaAidMortuary;

use App\Models\Admin\User;
use App\Models\ObservationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaAidObservation extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'quota_aid_mortuary_id',
        'observation_type_id',
        'date',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function quota_aid_mortuary()
    {
        return $this->belongsTo(QuotaAidMortuary::class);
    }
    public function observation_type()
    {
        return $this->belongsTo(ObservationType::class);
    }
}
