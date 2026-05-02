<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::query();
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }
        if ($request->query('per_page') === 'all') {
            $attendances = $query->get();
        } else {
            $perPage = $request->query('per_page', 10);
            $attendances = $query->paginate($perPage);
        }
        return response()->json([
            'message' => 'Attendances retrieved successfully.',
            'data' => $attendances
        ], 200);
    }

    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'is_absent' => 'boolean',
            'absence_reason' => 'required_if:is_absent,true|in:sick,personal,other',
            'notes' => 'required_if:absence_reason,other|nullable|string'
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
                'error' => 'Attendance for today has already been recorded.'
            ], 409);
        }
        $status = 'present';
        $clockInTime = $now;
        $finalNotes = $request->notes;
        if ($request->is_absent) {
            $status = 'absent';
            $clockInTime = null;
            if ($request->absence_reason === 'other') {
                $finalNotes = $request->notes;
            } else {
                $reasonLabel = $request->absence_reason === 'sick' ? 'Sick' : 'Having personal matters';
                $finalNotes = $reasonLabel . ($request->notes ? ' - ' . $request->notes : '');
            }
        } else {
            $lateLimit = '08:00:00';
            if ($now > $lateLimit) {
                $status = 'late';
            }
        }
        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $today,
            'clock_in' => $clockInTime,
            'status' => $status,
            'notes' => $finalNotes
        ]);
        return response()->json([
            'message' => $status === 'absent' ? 'Absence recorded successfully.' : 'Clock-in successful.',
            'data' => $attendance
        ], 201);
    }

    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->whereIn('status', ['present', 'late'])
            ->whereNull('clock_out')
            ->first();
        if (!$attendance) {
            $existing = Attendance::where('employee_id', $request->employee_id)->first();
            if ($existing && $existing->clock_out) {
                return response()->json(['error' => 'Already clocked out today.'], 409);
            }
            if ($existing && $existing->status === 'absent') {
                return response()->json(['error' => 'Cannot clock out because employee is marked as absent today.'], 400);
            }
            return response()->json(['error' => 'No active attendance record found to clock out.'], 404);
        }
        $attendance->update(['clock_out' => Carbon::now()->toTimeString()]);
        return response()->json([
            'message' => 'Clock-out successful.',
            'data' => $attendance
        ], 200);
    }
}
