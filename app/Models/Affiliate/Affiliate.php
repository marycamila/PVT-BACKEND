<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\FinancialEntity;
use App\Models\Contribution\PayrollSenasir;
use App\Models\Admin\User;
use App\Models\Contribution\AidContribution;
use App\Models\Contribution\AidReimbursement;
use App\Models\Contribution\Contribution;
use App\Models\Contribution\Reimbursement;
use App\Models\Affiliate\Address;
use App\Models\Contribution\PayrollCommand;

class Affiliate extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'affiliate_state_id',
        'city_identity_card_id',
        'city_birth_id',
        'degree_id',
        'unit_id',
        'category_id',
        'pension_entity_id',
        'identity_card',
        'registration',
        'type',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'gender',
        'civil_status',
        'birth_date',
        'date_entry',
        'date_death',
        'reason_death',
        'date_derelict',
        'reason_derelict',
        'phone_number',
        'cell_phone_number',
        'nua',
        'created_at',
        'updated_at',
        'deleted_at',
        'service_years',
        'service_months',
        'death_certificate_number',
        'due_date',
        'is_duedate_undefined',
        'account_number',
        'financial_entity_id',
        'sigep_status',
        'unit_police_description',
        'id_person_senasir'
    ];

    public function affiliate_state()
    {
        return $this->belongsTo(AffiliateState::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function pension_entity() 
    {
        return $this->belongsTo(PensionEntity::class);
    }
    public function financial_entity()
    {
        return $this->belongsTo(FinancialEntity::class);
    }
    public function payroll_senasir()
    {
        return $this->hasMany(PayrollSenasir::class);
    }
    public function contributions()        
    {
      return $this->hasMany(Contribution::class);
    }
    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class);
    }
    public function addresses()
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps()->latest('updated_at');
    }
    public function payroll_command()
    {
        return $this->hasMany(PayrollSenasir::class);
    }
}
