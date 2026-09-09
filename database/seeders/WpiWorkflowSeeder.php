<?php

namespace Database\Seeders;

use App\Models\WpiWorkflow;
use Illuminate\Database\Seeder;

class WpiWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflows = [
            // [from_status, from_inisial, to_status, to_inisial, role, validate]

            // Sequence 1: Submitted (Role: Submitter)
            ['Submitted', 'Submitted', 'Assigned', 'Assign to ERM', 'Submitter', true],
            ['Submitted', 'Submitted', 'Closed', 'Closed Event', 'Submitter', true],

            // Sequence 2: Assigned (Role: Event Report Manager)
            ['Assigned', 'Assigned', 'Final Review', 'Final Review', 'Event Report Manager', true],
            ['Assigned', 'Assigned', 'Review Event', 'Re-Assign to ERM', 'Event Report Manager', false],

            // Sequence 3: Final Review (Role: Moderator)
            ['Final Review', 'Final Review', 'Closed', 'Closed', 'Moderator', true],
            ['Final Review', 'Final Review', 'Assigned', 'Re-assign ERM', 'Moderator', false],

            // Sequence 4: Closed (Role: Moderator)
            ['Closed', 'Closed', 'Final Review', 'Re-open Event', 'Moderator', true],
            ['Closed', 'Closed', 'Cancelled', 'Cancelled', 'Moderator', true],

            // Sequence 5: Cancelled (Role: Event Report Manager)
            ['Cancelled', 'Cancelled', 'Review Event', 'Re-Open', 'Event Report Manager', false],

            // Sequence 6: Review Event (Role: Event Report Manager)
            ['Review Event', 'Re-Open Event', 'Assigned', 'Assign to ERM', 'Event Report Manager', true],
            ['Review Event', 'Re-Open Event', 'Closed', 'Closed Event', 'Event Report Manager', true],
        ];

        foreach ($workflows as [$from, $from_ini, $to, $to_ini, $role, $val]) {
            WpiWorkflow::create([
                'from_status'  => $from,
                'from_inisial' => $from_ini,
                'to_status'    => $to,
                'to_inisial'   => $to_ini,
                'role'         => $role,
                'validate_transition' => $val,
            ]);
        }
    }
}
