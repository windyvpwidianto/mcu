<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Menu;
use App\Models\SubMenu;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menuMcu = Menu::where('menu', 'like', '%MCU%')->first();
        if ($menuMcu) {
            SubMenu::firstOrCreate(
                [
                    'menu_id' => $menuMcu->id,
                    'route'   => 'mcu.contractor-email',
                ],
                [
                    'menu'          => 'Email Kontraktor',
                    'icon'          => 'envelope',
                    'request_route' => 'mcu/contractor-email*',
                    'status'        => 'enabled',
                    'urutan'        => 5,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SubMenu::where('route', 'mcu.contractor-email')->delete();
    }
};
