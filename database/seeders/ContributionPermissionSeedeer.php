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
                'action_id'=>Action::where('name','create')->first()->id,
                'name' => 'create-import-senasir',
                'display_name' => 'Crear importacion senasir'
            ], [
                'action_id'=>Action::where('name','create')->first()->id,
                'name' => 'Create-import-command',
                'display_name' => 'Crear importacion Comando'
            ],
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
