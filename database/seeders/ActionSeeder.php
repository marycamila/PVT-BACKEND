<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = [
            [
                'name' => 'download'
            ],
        ];
        foreach ($actions as $action) {
            DB::table('actions')
                ->updateOrInsert(
                    ['name' => $action],
                );
        }
    }
}