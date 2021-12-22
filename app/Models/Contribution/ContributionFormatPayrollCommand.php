<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContributionFormatPayrollCommand extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = ['uni','desg','mes','a_o','car','pat','mat','apes','nom','nom2','eciv','niv','gra','sex','sue','cat','est','carg','fro','ori','bseg','gan','mus','lpag','nac','ing'];

}
