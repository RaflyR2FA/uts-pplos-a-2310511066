<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();
        $existing = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $today)
            ->first();
        if ($existing) {
            return response()->json([
                'error' => 'Already clocked in today.'
            ], 409);
        }
        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $today,
            'clock_in' => $now,
            'status' => 'present',
            'notes' => $request->notes
        ]);
        return response()->json([
            'message' => 'Clock-in successful.',
            'data' => $attendance
        ], 201);
    }

    public function clockOut(Request $request, int $id)
    {
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return response()->json([
                'error' => 'Attendance record not found.'
            ], 404);
        }
        if ($attendance->clock_out) {
            return response()->json([
                'error' => 'Already clocked out.'
            ], 409);
        }
        $attendance->update(['clock_out' => Carbon::now()->toTimeString()]);
        return response()->json([
            'message' => 'Clock-out successful.',
            'data' => $attendance
        ], 200);
    }
}
