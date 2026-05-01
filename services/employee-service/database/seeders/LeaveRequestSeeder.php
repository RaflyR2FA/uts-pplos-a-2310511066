<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::inRandomOrder()->take(3)->get();
        foreach ($employees as $employee) {
            LeaveRequest::create([
                'employee_id' => $employee->id,
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'type' => 'annual',
                'reason' => 'Family vacation',
                'status' => 'pending',
                'approved_by' => null,
            ]);
        }
    }
}
