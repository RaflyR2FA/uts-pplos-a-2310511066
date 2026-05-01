<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $today = Carbon::today();
        for ($i = 0; $i < 5; $i++) {
            $date = $today->copy()->subDays($i);
            if ($date->isWeekend()) continue;
            foreach ($employees as $employee) {
                $statuses = ['present', 'present', 'present', 'late', 'absent'];
                $status = $statuses[array_rand($statuses)];
                $clockIn = null;
                $clockOut = null;
                if ($status === 'present') {
                    $clockIn = '08:00:00';
                    $clockOut = '17:00:00';
                } elseif ($status === 'late') {
                    $clockIn = '09:15:00';
                    $clockOut = '17:30:00';
                }
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'notes' => $status === 'absent' ? 'Sick leave' : null,
                ]);
            }
        }
    }
}
