<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Contribution\ContributionState;

class ContributionStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $contribution_states = [
             [
                 'name' => 'En proceso',
                 'description' => 'Aporte en Proceso'
             ],[
                 'name' => 'Pagado',
                 'description' => 'Aporte Pagado'
             ], [
                 'name' => 'Devuelto',
                 'description' => 'Aporte Devuelto al Titular o Beneficiario'
             ]
         ];
         foreach ($contribution_states as $contribution_state) {
            ContributionState::firstOrCreate($contribution_state);
         }
    }
}
