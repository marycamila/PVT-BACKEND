<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Contribution\ContributionOrigin;
use App\Models\Affiliate\PensionEntity;
class ContributionOriginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pension_entities = PensionEntity::get();
        foreach($pension_entities as $pension_entity){
            if($pension_entity->name == "AFP FUTURO"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_afp_futuro',
                        'type' => 'COMPLEMENTO ECONÓMICO',
                        'description' => 'Complemento Económico Pensión de afp Futuro',
                        'shortened' => 'CE-AF',
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_afp_futuro',
                        'type' => 'DIRECTO',
                        'description' => 'Aporte Directo Pensión afp Futuro',
                        'shortened' => 'DC-AF',
                    ],
                ];
            }
            if($pension_entity->name == "AFP PREVISIÓN"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_afp_prevision',
                        'type' => 'COMPLEMENTO ECONÓMICO',
                        'description' => 'Complemento Económico Pensión de afp Previsión',
                        'shortened' => 'CE-AP',
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_afp_prevision',
                        'type' => 'DIRECTO',
                        'description' => 'Aporte Directo Pensión afp previsión',
                        'shortened' => 'DC-AP',
                    ],
                ];
            }
            if($pension_entity->name == "LA VITALICIA"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_vitalicia',
                        'type' => 'COMPLEMENTO ECONÓMICO',
                        'description' => 'Complemento Económico Pensión de la Vitalicia',
                        'shortened' => 'CE-V',
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_vitalicia',
                        'type' => 'DIRECTO',
                        'description' => 'Aporte Directo Pensión de la Vitalicia',
                        'shortened' => 'DC-V',
                    ],
                ];
            }
            if($pension_entity->name == "PROVIDA"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_provida',
                        'type' => 'COMPLEMENTO ECONÓMICO',
                        'description' => 'Complemento Económico Pensión de Próvida',
                        'shortened' => 'CE-P',
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_provida',
                        'type' => 'DIRECTO',
                        'description' => 'Aporte Directo Pensión Próvida',
                        'shortened' => 'DC-P',
                    ],
                ];
            }
            if($pension_entity->name == "SENASIR"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'senasir',
                        'type' => 'PLANILLA',
                        'description' => 'Aporte Senasir',
                        'shortened' => 'SEN',
                     ]
                    ];
            }
            foreach ($contribution_origins as $contribution_origin) {
                ContributionOrigin::firstOrCreate($contribution_origin);
            }
        }
    }
}
