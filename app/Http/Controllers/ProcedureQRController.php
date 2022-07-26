<?php

namespace App\Http\Controllers;

use App\Models\Admin\Role;
use App\Models\Loan\Loan;
use App\Models\Loan\LoanBorrower;
use App\Models\Loan\LoanState;
use App\Models\Procedure\ProcedureModality;
use Illuminate\Http\Request;

class ProcedureQRController extends Controller
{
    public function procedure_qr(Request $request)
    {   
        $request->validate([
            'uuid' => 'required|uuid',
            'module_id' => 'required|integer'
        ]);

        switch ($request->module_id) {
            case 6:
                $data = Loan::where('uuid',$request->uuid)->first();
                $state = LoanState::find($data->state_id);
                $procedure = ProcedureModality::find($data->procedure_modality_id);
                $type = Loan::find($data->id)->modality->procedure_type->name;
                $borrower = LoanBorrower::find($data->id)->FullName;
                $role = Role::find($data->role_id);
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
                return 'No encontrado';
        }

        return response()->json([
            'message' => 'Trámite encontrado',
            'payload' => [
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