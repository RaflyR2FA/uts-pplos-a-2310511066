<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with('employee');
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $leaves = $query->paginate($request->input('per_page', 10));
        return response()->json([
            'message' => 'Leave requests retrieved successfully.',
            'data' => $leaves
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:annual,sick,unpaid,other',
            'reason' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $leaveData = $request->all();
        $leaveData['status'] = 'pending';
        $leave = LeaveRequest::create($leaveData);
        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data' => $leave
        ], 201);
    }

    public function show(int $id)
    {
        $leave = LeaveRequest::with('employee')->find($id);
        if (!$leave) {
            return response()->json([
                'error' => 'Leave request not found.'
            ], 404);
        }
        return response()->json([
            'message' => 'Leave request detail retrieved successfully.',
            'data' => $leave
        ], 200);
    }

    public function updateApproval(Request $request, int $id)
    {
        $leave = LeaveRequest::find($id);
        if (!$leave) {
            return response()->json([
                'error' => 'Leave request not found.'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'approved_by' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        if ($leave->status !== 'pending') {
            return response()->json([
                'error' => 'Leave request has already been processed.'
            ], 409);
        }
        $leave->update([
            'status' => $request->status,
            'approved_by' => $request->approved_by
        ]);
        return response()->json([
            'message' => 'Leave request status updated successfully.',
            'data' => $leave
        ], 200);
    }
}
