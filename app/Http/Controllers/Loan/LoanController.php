<?php

namespace App\Http\Controllers\Loan;

use App\Http\Controllers\Controller;
use App\Models\Loan\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
    public function print_plan(Request $request, Loan $loan)
    {
        if($loan->disbursement_date){
            $procedure_modality = $loan->modality;
            $file_title = implode('_', ['PLAN','DE','PAGOS', $procedure_modality->shortened, $loan->code,Carbon::now()->format('m/d')]);
            $is_dead = false;
            if($loan->borrower->first()->type == 'spouses')
                $is_dead = true;
            $data = [
                'header' => [
                    'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                    'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                    'table' => [
                        ['Tipo', $loan->modality->procedure_type->second_name],
                        ['Modalidad', $loan->modality->shortened],
                        ['Fecha', Carbon::now()->format('d/m/Y')],
                        ['Hora', Carbon::now()->format('H:i')],
                    ]
                ],
                'title' => 'PLAN DE PAGOS',
                'loan' => $loan,
                'lender' => $is_dead ? $loan->affiliate->spouse : $loan->affiliate,
                'is_dead'=> $is_dead,
                'file_title'=>$file_title
            ];
            $file_name = implode('_', ['plan', $procedure_modality->shortened, $loan->code]) . '.pdf';
            $pdf=PDF::loadView('loan/payment_plan', $data);
            return $pdf->stream();
        }else{
            return "Prestamo no desembolsado";
        }
    }

     /**
     * @OA\Get(
     *     path="/api/app/get_information_loan/{id_affiliate}",
     *     tags={"OFICINA VIRTUAL"},
     *     summary="LISTADO DE PRESTAMOS DE UN AFILIADO",
     *     operationId="get_information_loan",
     * @OA\Parameter(
     *         name="id_affiliate",
     *         in="path",
     *         description="",
     *         example=1,
     *
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format = "int64"
     *         )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         type="object"
     *         )
     *     ),
     * )
     *
     * Get status of virtual office.
     *
     * @param Request $request
     * @return void
     */
    public function get_information_loan(Request $request, $id_affiliate)
    {
        $request['affiliate_id'] = $id_affiliate;
        $hasLoans = DB::table('loans')->where('affiliate_id',$request->id_affiliate)->exists();
        if ($hasLoans) {
            $loans = Loan::where([
                ['affiliate_id', '=',$request->id_affiliate]
            ])->whereIn('state_id',[3,4])->get();
        $allLoans=[];
        foreach ($loans as $loan ) {
            array_push($allLoans,array(
                "id"=> $loan->id,
                "code"=> $loan->code,
                "procedure_modality" => $loan->modality->name,
                "request_date"=> $loan->disbursement_date,
                "amount_requested"=> $loan->amount_requested,
                "city"=> $loan->city->name,
                "interest"=> $loan->interest->annual_interest,
                "state"=> $loan->state->name,
                "amount_approved"=> $loan->amount_approved,
                "liquid_qualification_calculated"=> $loan->liquid_qualification_calculated,
                "loan_term"=> $loan->loan_term,
                "refinancing_balance"=> $loan->refinancing_balance,
                "payment_type"=> $loan->payment_type->name,
                "destiny_id"=> $loan->destiny->name,
                "quota"=> $loan->EstimatedQuota,
                )
            );
        }
        return response()->json([
            'error' => 'false',
            'message' => 'Lista de Prestamos',
            'payload' => $allLoans,
        ]);
        }
        else{
            return response()->json([
                'error' => 'true',
                'message' => 'El afiliado no tiene prestamos',
                'payload' => [
                ],
            ]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
