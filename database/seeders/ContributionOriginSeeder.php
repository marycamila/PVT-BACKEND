<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ContributionOriginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   $pension_entities = "select * from pension_entities";
        $pension_entities = DB::select($pension_entities);
        
        foreach($pension_entities as $pension_entity){
            if($pension_entity->name == "AFP FUTURO"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_afp_futuro',
                        'description' => 'Complemento Económico Pensión de afp Futuro',
                        'shortened' => 'CE-AF',        
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_afp_futuro',
                        'description' => 'Aporte Directo Pensión afp Futuro',
                        'shortened' => 'DC-AF',        
                    ],
                ];
            }
            if($pension_entity->name == "AFP PREVISIÓN"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_afp_prevision',
                        'description' => 'Complemento Económico Pensión de afp Previsión',
                        'shortened' => 'CE-AP',        
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_afp_prevision',
                        'description' => 'Aporte Directo Pensión afp previsión',
                        'shortened' => 'DC-AP',        
                    ],
                ];
            }
            if($pension_entity->name == "LA VITALICIA"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_vitalicia',
                        'description' => 'Complemento Económico Pensión de la Vitalicia',
                        'shortened' => 'CE-V',        
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_vitalicia',
                        'description' => 'Aporte Directo Pensión de la Vitalicia',
                        'shortened' => 'DC-V',        
                    ],
                ];
            }
            if($pension_entity->name == "PROVIDA"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'eco_com_provida',
                        'description' => 'Complemento Económico Pensión de Próvida',
                        'shortened' => 'CE-P',      
                    ],[
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'direct_con_provida',
                        'description' => 'Aporte Directo Pensión Próvida',
                        'shortened' => 'DC-P',        
                    ],
                ];
            }
            if($pension_entity->name == "SENASIR"){
                    $contribution_origins = [ [
                        'pension_entity_id'=> $pension_entity->id,
                        'name' => 'senasir',
                        'description' => 'Aporte Senasir',
                        'shortened' => 'SEN',        
                     ]
                    ];
            }  
            DB::table('contribution_origins')->insert($contribution_origins);     
        }           
    }
}
