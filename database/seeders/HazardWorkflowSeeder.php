<?php

namespace Database\Seeders;

use App\Models\HazardWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HazardWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workflows = [
            ['submitted', 'Moderator Review', 'in_progress', 'Assigned to ERM', 'moderator'],
            ['pending', 'Moderator Verification', 'in_progress', 'Re-Assigned to ERM', 'moderator'],
            ['pending', 'Moderator Verification', 'closed', 'Close Event', 'moderator'],
            ['submitted', 'Moderator Review', 'cancelled', 'Cancel Event', 'moderator'],
            ['pending', 'Moderator Verification', 'cancelled', 'Cancel Event', 'moderator'],
            ['closed', 'Closed Event', 'submitted', 'Re-Open Event', 'moderator'],
            ['closed', 'Closed Event', 'cancelled', 'Cancel Event', 'moderator'],
            ['cancelled', 'Cancelled Event', 'submitted', 'Re-Open Event', 'moderator'],
            ['cancelled', 'Cancelled Event', 'closed', 'Close Event', 'moderator'],
            ['in_progress', 'ERM Assigned', 'pending', 'Moderator Verification', 'erm'],
            ['in_progress', 'Moderator Verification', 'closed', 'Closed Event', 'erm'],
        ];

        foreach ($workflows as [$from, $from_inisial, $to, $to_inisial, $role]) {
            HazardWorkflow::firstOrCreate([
                'from_status' => $from,
                'from_inisial' => $from_inisial,
                'to_status' => $to,
                'to_inisial' => $to_inisial,
                'role'       => $role,
            ]);
        }
    }
}
