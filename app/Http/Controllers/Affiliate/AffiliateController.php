<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Affiliate\Affiliate;
use App\Models\Affiliate\AffiliateToken;
use App\Models\Affiliate\AffiliateUser;
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
        $id_affiliate = request('id_affiliate') ?? '';
        $identity_card_affiliate = request('identity_card_affiliate') ?? '';
        $registration_affiliate = request('registration_affiliate') ?? '';
        $full_name_affiliate  = request('full_name_affiliate') ?? '';
        $name_degree = request('name_degree') ?? '';
        $name_affiliate_state = request('name_affiliate_state') ?? '';

        $conditions = [];

        if ($id_affiliate != '') {
            array_push($conditions, array('view_affiliates.id_affiliate', $id_affiliate));
        }
        if ($identity_card_affiliate != '') {
            array_push($conditions, array('view_affiliates.identity_card_affiliate', 'ilike', "%{$identity_card_affiliate}%"));
        }
        if ($registration_affiliate != '') {
            array_push($conditions, array('view_affiliates.registration_affiliate', 'ilike', "%{$registration_affiliate}%"));
        }
        if ($full_name_affiliate != '') {
            array_push($conditions, array('view_affiliates.full_name_affiliate', 'ilike', "%{$full_name_affiliate}%"));
        }
        if ($name_degree != '') {
            array_push($conditions, array('view_affiliates.name_degree', 'ilike', "%{$name_degree}%"));
        }
        if ($name_affiliate_state != '') {

            array_push($conditions, array('view_affiliates.name_affiliate_state', 'ilike', "%{$name_affiliate_state}%"));
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
        $affiliate->full_name = $affiliate->full_name;
        $affiliate->civil_status_gender = $affiliate->civil_status_gender;
        $affiliate->identity_card_ext = $affiliate->identity_card_ext;
        $affiliate->degree = $affiliate->degree;
        $affiliate->category = $affiliate->category;
        $affiliate->unit = $affiliate->unit;
        $affiliate->addresses = $affiliate->addresses;
        $affiliate->addresses->city = $affiliate->addresses->first()->city;

        //$affiliate->cell_phone1 = explode(',', $affiliate->cell_phone_number);

        //$data = $affiliate->cell_phone_number;
        $affiliate->cell_phone_number = explode(',', $affiliate->cell_phone_number);

              

        if ($affiliate->spouse) {
            $affiliate->spouse = $affiliate->spouse;
            $affiliate->dead_spouse = $affiliate->spouse->dead;
        } else {
            $affiliate->spouse = [];
            $affiliate->dead_spouse = null;
        }
        if ($affiliate->affiliate_state != null) $affiliate->affiliate_state;
        if ($affiliate->affiliate_state != null) $affiliate->dead = $affiliate->dead;

        return $affiliate;
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
    /**
     * @OA\Get(
     *     path="/api/affiliate/access_status/{id}",
     *     tags={"AFILIADO"},
     *     summary="ESTADO DE CREDENCIALES - OFICINA VIRTUAL",
     *     operationId="getStatus",
     * @OA\Parameter(
     *         name="id",
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
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * Get status of virtual office.
     *
     * @param Request $request
     * @return void
     */
    public function access_status(Request $request, $id)
    {
        $request['id'] = $id;
        $request->validate([
            'id' => 'required|integer|exists:affiliates,id'
        ]);

        $data = Affiliate::find($id);
        $affiliate_token = AffiliateToken::whereAffiliateId($id)->first()->id;
        $affiliate_user = AffiliateUser::where('affiliate_token_id', $affiliate_token)->first();
        if ($affiliate_user == NULL) {
            $access = "No tiene credenciales";
            $created = "";
            $updated = "";
        } else {
            $access = $affiliate_user->access_status;
            $created = $affiliate_user->created_at;
            $updated = $affiliate_user->updated_at;
        }
        $data->access_status = $access;
        $data->created = $created;
        $data->updated = $updated;

        return response()->json([
            'message' => 'Affiliado encontrado',
            'payload' => [
                'full_name' => $data->fullName,
                'identity_card' => $data->identity_card,
                'affiliate_state' => $data->affiliate_state->name,
                'access_status' => $data->access_status,
                'created_at' => $data->created,
                'updated_at' => $data->updated
            ],
        ]);
    }
}
