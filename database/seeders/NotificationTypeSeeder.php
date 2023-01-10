<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Notification\NotificationType;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notification_types')->truncate();
        DB::statement('alter sequence notification_types_id_seq restart with 1');
        $notification_types = [
            [
                'type' => 'Recepción de requisitos',
            ],
            [
                'type' => 'Pago de complemento económico',
            ],
            [
                'type' => 'Observación de complemento económico',
            ],
            [
                'type' => 'Desembolso de préstamo',
            ],
            [
                'type' => 'Contrato de préstamo',
            ],
        ];
        foreach ($notification_types as $notification_type) {
            NotificationType::firstOrCreate($notification_type);
        }
    }
}
