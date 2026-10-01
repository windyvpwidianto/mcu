<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\McuRecord;
use App\Models\McuResult;
use App\Notifications\McuResultNotification;

$user = User::where('name', 'like', '%Fiska%')->orWhere('name', 'like', '%Stella%')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}
echo "User found: " . $user->name . "\n";

// Update her phone number
$user->update(['phone_number' => '085256944911']);
echo "Updated phone number to 085256944911\n";

$mcuRecord = McuRecord::firstOrCreate(
    ['employee_id' => $user->id, 'mcu_year' => 2026],
    [
        'mcu_date' => \Carbon\Carbon::today()->subDays(2)->toDateString(),
        'attendance_status' => 'present',
        'process_status' => 'completed',
        'notification_status' => 'pending',
    ]
);

$mcuResult = McuResult::firstOrCreate(
    ['mcu_record_id' => $mcuRecord->id],
    [
        'status' => 'fit_to_work',
        'recommendations' => 'Keep up the good work.',
        'is_published' => true,
    ]
);

$mcuResult->update([
    'status' => 'fit_to_work'
]);

echo "Sending notification...\n";
$user->notifyNow(new McuResultNotification($mcuResult, 'employee', [\App\Channels\WhatsAppChannel::class]));
echo "Notification sent to Fiska!\n";

