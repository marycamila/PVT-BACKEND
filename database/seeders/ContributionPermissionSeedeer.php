<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Admin\Permission;
use \App\Models\Admin\Action;

class ContributionPermissionSeedeer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
           [
                'action_id'=>Action::where('name','read')->first()->id,
                'name' => 'read-import-contribution',
                'display_name' => 'Ver importación de aportes'
            ],[
                'action_id'=>Action::where('name','create')->first()->id,
                'name' => 'create-import-senasir',
                'display_name' => 'Crear importacion senasir'
            ], [
                'action_id'=>Action::where('name','create')->first()->id,
                'name' => 'create-import-command',
                'display_name' => 'Crear importacion Comando'
            ], [
                'action_id'=>Action::where('name','download')->first()->id,
                'name' => 'download-report-senasir',
                'display_name' => 'Descarga reporte senasir'
            ], [
                'action_id'=>Action::where('name','download')->first()->id,
                'name' => 'download-report-command',
                'display_name' => 'Descarga reporte comando general'
            ], [
                'action_id'=>Action::where('name','read')->first()->id,
                'name' => 'read-import-payroll',
                'display_name' => 'Ver importación de planillas'
            ],[
                'action_id'=>Action::where('name','create')->first()->id,
                'name' => 'create-import-payroll-senasir',
                'display_name' => 'crear importación planilla senasir'
            ],[
                'action_id'=>Action::where('name','create')->first()->id,
                'name' => 'create-import-payroll-command',
                'display_name' => 'crear importación planilla comando general'
            ],[
                'action_id'=>Action::where('name','download')->first()->id,
                'name' => 'download-report-payroll-senasir',
                'display_name' => 'Descarga reporte planilla senasir'
            ],[
                'action_id'=>Action::where('name','download')->first()->id,
                'name' => 'download-report-payroll-command',
                'display_name' => 'Descarga reporte planilla comando general'
            ],
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
