<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Affiliate\Affiliate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class AffiliateController extends Controller
{
 /**
     * @OA\Get(
     *     path="/api/affiliate/affiliate",
     *     tags={"AFILIADO"},
     *     summary="LISTADO DE AFILIADOS",
     *     operationId="getAffiliates",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página a mostrar",
     *         example=1,
     *         required=false, 
     *       ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Por Página",
     *         example=10,
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="sortDesc",
     *         in="query",
     *         description="Vector de orden descendente(0) o ascendente(1)",
     *         example=1,
     *         required=false,
     *     ),
     *    @OA\Parameter(
     *         name="id_affiliate",
     *         in="query",
     *         description="Filtro por id del Afiliado",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="identity_card_affiliate",
     *         in="query",
     *         description="Filtro por Cédula de Identidad",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="registration_affiliate",
     *         in="query",
     *         description="Filtro por Matrícula",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="full_name_affiliate",
     *         in="query",
     *         description="Filtro por Nombre o Apellido",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="name_degree",
     *         in="query",
     *         description="Filtro por grado del Afiliado",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="name_affiliate_state",
     *         in="query",
     *         description="Filtro por estado del Afiliado",
     *         required=false,
     *     ),
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
     * Get list of affiliates.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $id_affiliate = request('id_affiliate')?? '';
        $identity_card_affiliate = request('identity_card_affiliate')?? '';
        $registration_affiliate = request('registration_affiliate') ?? '';
        $full_name_affiliate  = request('full_name_affiliate')?? '';
        $name_degree = request('name_degree')?? '';
        $name_affiliate_state = request('name_affiliate_state')?? '';

        $conditions = [];

        if ($id_affiliate != '') {
            array_push($conditions, array('view_affiliates.id_affiliate', $id_affiliate));
        }
        if ($identity_card_affiliate != '') {
            array_push($conditions, array('view_affiliates.identity_card_affiliate','ilike',"%{$identity_card_affiliate}%"));
        }
        if ($registration_affiliate != '') {
          array_push($conditions, array('view_affiliates.registration_affiliate','ilike',"%{$registration_affiliate}%"));
        }
        if ($full_name_affiliate != '') {
            array_push($conditions, array('view_affiliates.full_name_affiliate','ilike',"%{$full_name_affiliate}%"));
        }
        if ($name_degree != '') {
            array_push($conditions, array('view_affiliates.name_degree','ilike',"%{$name_degree}%"));  
        }  
        if ($name_affiliate_state != '') {
 
            array_push($conditions, array('view_affiliates.name_affiliate_state','ilike',"%{$name_affiliate_state}%"));
        }  
        
        $order = request('sortDesc') ?? '';
        if ($order != '') {
            if ($order) {
                $order_affiliate = 'Asc';
            }
            if (!$order) {
                $order_affiliate = 'Desc';
            }
        } else {
            $order_affiliate = 'Desc';
        }  

        $per_page = $request->per_page ?? 10;      
        $affiliates = DB::table('view_affiliates')
        ->where($conditions)
        ->select('*')
        ->orderBy('full_name_affiliate', $order_affiliate)
        ->paginate($per_page);        

        return response()->json([
            'message' => 'Realizado con éxito',
            'payload' => [
                'affiliates' => $affiliates
            ],
        ]);
    }

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
    public function show(Affiliate $affiliate)
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
