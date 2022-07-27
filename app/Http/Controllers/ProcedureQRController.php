<?php

namespace App\Http\Controllers;

use App\Models\Admin\Module;
use App\Models\Admin\Role;
use App\Models\Loan\Loan;
use App\Models\Loan\LoanBorrower;
use App\Models\Loan\LoanState;
use App\Models\Procedure\ProcedureModality;
use App\Models\RetirementFund\RetFunState;
use App\Models\RetirementFund\RetirementFund;
use Illuminate\Http\Request;

class ProcedureQRController extends Controller
{
    public function procedure_qr(Request $request,$module_id,$uuid)
    {   
        $request['module_id'] = $module_id;
        $request['uuid'] = $uuid;
        $request->validate([
            'module_id' => 'required|integer|exists:modules,id'
        ]);

        switch ($module_id) {
            case 6:
                $request->validate([
                    'uuid' => 'required|uuid|exists:loans,uuid'
                ]);
                $module = Module::find($module_id);
                $data = Loan::where('uuid',$uuid)->first();
                $state = LoanState::find($data->state_id);
                $procedure = ProcedureModality::find($data->procedure_modality_id);
                $type = Loan::find($data->id)->modality->procedure_type->name;
                $borrower = LoanBorrower::where('loan_id',$data->id)->first()->FullName;
                $role = Role::find($data->role_id);
                $data->module_display_name = $module->display_name;
                $data->code = $data->code;
                $data->state_name = $state->name;
                $data->procedure_modality_name = $procedure->name;
                $data->procedure_type_name = $type;
                $data->person = $borrower;
                $data->location =$role->display_name;
                break;

            case 4:
                $data = 'Cuota y Auxilio Mortuorio';
                break;
            
            case 3:
                $data = 'Fondo de Retiro';
                break;

            case 2:
                $data = 'Complemento Económico';
                break;

            default:
                return 'Trámite no encontrado';
        }

        return response()->json([
            'message' => 'Trámite encontrado',
            'payload' => [
                'module_display_name' => $data->module_display_name,
                'person' => $data->person,
                'code' => $data->code,
                'procedure_modality_name' => $data->procedure_modality_name,
                'procedure_type_name' => $data->procedure_type_name,
                'location' => $data->location,
                'validated' => $data-> validated,
                'state_name' => $data->state_name
            ],
        ]);
    }
 
}