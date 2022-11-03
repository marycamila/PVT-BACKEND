<?php

namespace App\Http\Controllers\Contribution;

use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\Affiliate\Affiliate;
use App\Models\Contribution\ContributionPassive;
use App\Models\Month;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributionPassiveController extends Controller
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
    public function show(Request $request)
    {
        $request->validate([
            'affiliate_id' => 'required|integer|exists:affiliates,id'
        ]);

        $affiliate = Affiliate::find($request->affiliate_id);
        $year_min = $this->get_minimum_year($request->affiliate_id);
        $year_max = $this->get_maximum_year($request->affiliate_id);

        $all_contributions = collect();
        $months = DB::table('months')->get();

        for ($i = $year_max; $i >= $year_min; $i--) {
            $contributions = collect();
            $contribution_passives = ContributionPassive::whereAffiliateId($request->affiliate_id)
                ->whereYear('month_year', $i)
                ->orderBy('month_year', 'asc')
                ->get();

            foreach ($months as $month) {
                $mes = (string)$month->id;
                $detail = collect();
                foreach ($contribution_passives as $contributions_passive) {
                    $m = ltrim(Carbon::parse($contributions_passive->month_year)->format('m'), "0");
                    // if (Str::contains($m, $mes)) {
                    if ($m == $mes) {
                        $detail->push($contributions_passive
                        );
                    }
                }
                $contributions->push([
                    'month' => $month->name,
                    'detail' => (object)$detail->first()
                ]);
            }
            $all_contributions->push([
                'year' => (String)$i,
                'contributions' => $contributions
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
                'all_contributions' => $all_contributions
            ],
        ]);
    }

    public function get_minimum_year($id)
    {
        $data = DB::table('contribution_passives')->where('affiliate_id', $id)->min('month_year');
        $min = Carbon::parse($data)->format('Y');

        return $min;
    }

    public function get_maximum_year($id)
    {
        $data = DB::table('contribution_passives')->where('affiliate_id', $id)->max('month_year');
        $max = Carbon::parse($data)->format('Y');

        return $max;
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
