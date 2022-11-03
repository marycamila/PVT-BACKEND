<?php

namespace App\Http\Controllers\Contribution;

use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\Affiliate\Affiliate;
use App\Models\Affiliate\Degree;
use App\Models\Contribution\Contribution;
use App\Models\Contribution\ContributionPassive;
use App\Models\Contribution\Reimbursement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContributionController extends Controller
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
     * @OA\Post(
     *      path="/api/contribution/active_affiliate_contribution",
     *      tags={"CONTRIBUCION"},
     *      summary="CONTRIBUCIONES DEL AFILIADO",
     *      operationId="getContributions",
     *      description="contribuciones del afiliado",
     *      @OA\RequestBody(
     *          description= "affiliate_id",
     *          required=true,
     *          @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *             @OA\Property(property="affiliate_id", type="integer",description="affiliate_id",example=123)
     *            )
     *          ),
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *            type="object"
     *         )
     *      )
     * )
     *
     * Logs user into the system.
     *
     * @param Request $request
     * @return void
     */

    public function show(Request $request)
    {
        $request->validate([
            'affiliate_id' => 'required|integer|exists:affiliates,id'
        ]);

        $affiliate = Affiliate::find($request->affiliate_id);
        $year_min = $this->get_minimum_year($request->affiliate_id);
        $year_max = $this->get_maximum_year($request->affiliate_id);
        $contribution_total = 0;

        $all_contributions = collect();

        $reimbursements = Reimbursement::whereAffiliateId($request->affiliate_id)
            ->orderBy('month_year', 'desc')
            ->get();
        $months = DB::table('months')->get();
        for ($i = $year_max; $i >= $year_min; $i--) {
            $full_total = 0;

            $contributions = collect();

            $contributions_actives = Contribution::whereAffiliateId($request->affiliate_id)
                ->whereYear('month_year', $i)
                ->get();

            foreach ($months as $month) {
                $mes = (string)$month->id;
                $detail = collect();
                foreach ($contributions_actives as $contributions_active) {
                    $contribution_total = $contributions_active->total;
                    $reimbursement_total = 0;
                    $full_total = $contributions_active->total;

                    foreach ($reimbursements as $reimbursement) {
                        if ($contributions_active->month_year == $reimbursement->month_year) {
                            $reimbursement_total = $reimbursement->total;
                            $full_total = $contribution_total + $reimbursement_total;
                        }
                    }
                    $m = ltrim(Carbon::parse($contributions_active->month_year)->format('m'), "0");
                    // if (Str::contains($m, $mes)) {
                    if ($m == $mes) {
                        $detail->push([
                            'id' => $contributions_active->id,
                            'month_year' => $contributions_active->month_year,
                            'quotable' => Util::money_format($contributions_active->quotable),
                            'retirement_fund' => Util::money_format($contributions_active->retirement_fund),
                            'mortuary_quo ta' => Util::money_format($contributions_active->mortuary_quota),
                            'reimbursement_total' => Util::money_format($reimbursement_total),
                            'total' => Util::money_format($contribution_total),
                            'contribution_total' => Util::money_format($full_total),
                            'type' => $contributions_active->contributionable_type
                        ]);
                    }
                }
                $contributions->push([
                    'month' => $month->name,
                    'detail' => $detail
                ]);
            }
            $all_contributions->push([
                'year' => $i . "",
                'contributions' => $contributions,
            ]);
        }

        return response()->json([
            'payload' => [
                'first_name' => $affiliate->first_name,
                'second_name' => $affiliate->second_name,
                'last_name' => $affiliate->last_name,
                'mothers_last_name' => $affiliate->mothers_last_name,
                'surname_husband' => $affiliate->surname_husband,
                'identity_card' => $affiliate->identity_card,
                'city_identity_card' => $affiliate->city_identity_card->first_shortened ?? '',
                'all_contributions' => $all_contributions
            ],
        ]);
    }

    public function get_minimum_year($id)
    {
        $data = DB::table('contributions')->where('affiliate_id', $id)->min('month_year');
        $min = Carbon::parse($data)->format('Y');
        return $min;
    }

    public function get_maximum_year($id)
    {
        $data2 = DB::table('contributions')->where('affiliate_id', $id)->max('month_year');
        $max2 = Carbon::parse($data2)->format('Y');
        return $max2;
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
