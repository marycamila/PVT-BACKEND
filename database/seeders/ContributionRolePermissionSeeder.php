<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Admin\Module;
use \App\Models\Admin\Role;
use Illuminate\Support\Str;

class ContributionRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //fondo de retiro 
        $module = Module::whereName('contribuciones')->first();
        $roles = [
            [
                'name' => 'Importación planilla',
                'action' => 'Importación planilla',
            ], [
                'name' => 'Importación aportes',
                'action' => 'Importación aportes',
            ]
        ];

        $permissions_payroll = ['read-import-payroll','create-import-payroll-senasir','download-report-payroll-senasir'];
        $permissions_senasir = ['read-import-contribution','create-import-senasir','download-report-senasir'];
        $permissions_global = ['read-import-contribution'];

        $sequence_permissions = ['update-affiliate-secondary', 'show-affiliate', 'show-loan', 'update-address', 'update-loan','show-history-loan'];
        foreach ($roles as $role) {
            $role = Role::firstOrCreate([
                'name' => $module->shortened . '-' . Str::slug($role['name'], '-')
            ], [
                'display_name' => $role['name'],
                'action' => $role['action'],
                'module_id' => $module->id,
            ]);

            if (in_array($role['display_name'], ['Importación planilla'])) {
                $role->syncPermissions(array_merge($permissions_payroll));
            }  elseif (in_array($role['display_name'], ['Importación aportes'])) {
                $role->syncPermissions(array_merge($permissions_senasir));
            }else {
                $role->syncPermissions($permissions_global);
            }
        }
    }
}
