<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('name', 'like', '%Omega%')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}
echo "User: " . $user->name . " (Phone: " . $user->phone_number . ")\n";

$mcu = App\Models\McuRecord::where('employee_id', $user->id)->first();
if (!$mcu) {
    echo "Creating MCU Record for Omega...\n";
    $mcu = App\Models\McuRecord::create([
        'employee_id' => $user->id,
        'mcu_year' => 2026,
        'mcu_date' => \Carbon\Carbon::today()->addDays(5)->toDateString(),
        'attendance_status' => 'scheduled',
        'process_status' => 'scheduled',
        'notification_status' => 'pending',
    ]);
} else {
    echo "Updating MCU Record for Omega...\n";
    $mcu->update([
        'mcu_date' => \Carbon\Carbon::today()->addDays(5)->toDateString(), 
        'notification_status' => 'pending' // reset to pending
    ]);
}

echo "Done updating. Running process-reminders...\n";
Artisan::call('mcu:process-reminders');
echo Artisan::output();
