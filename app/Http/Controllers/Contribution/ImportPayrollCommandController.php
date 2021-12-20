<?php

namespace App\Http\Controllers\Contribution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Contribution\ContributionCopyPayrollCommand;
use Carbon\Carbon;
use DateTime;

class ImportPayrollCommandController extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/contribution/period_copy_payroll_upload_command",
     *     tags={"CONTRIBUCION"},
     *     summary="PERIODO DE LA CONTRIBUCION",
     *     operationId="period_upload_command",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get list of modules.
     *
     * @param Request $request
     * @return void
     */
    public function period_copy_payroll_upload_command(request $request){
        $last_iportation =  ContributionCopyPayrollCommand::orderBy('id')->get()->last();
        if($last_iportation){
          $last_year = $last_iportation->a_o;
          $year = DateTime::createFromFormat('y', $last_year);
          $last_date = Carbon::parse($year->format('Y').'-'.$last_iportation->mes);
          $estimated_date = $last_date->addMonth();
        }else{
          $estimated_date = Carbon::now()->subMonth();
        }
        return response()->json([
          'message' => 'Realizado con exito',
          'payload' => [
              'estimated_date' => $estimated_date
          ]
        ]); 
    }
}
