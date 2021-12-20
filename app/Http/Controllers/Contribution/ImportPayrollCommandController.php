<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Contribution\ContributionCopyPayrollCommand;
use Carbon\Carbon;

class ImportPayrollCommandController extends Controller
{
    
    public function period_upload_command(request $request){
      $last_iportation =  ContributionCopyPayrollCommand::orderBy('id')->get()->last();
      $last_date = Carbon::parse($last_iportation->a_o.'-'.$last_iportation->mes);
      $estimated_date = $last_date->addMonth();
      return $estimated_date;
    }
}
