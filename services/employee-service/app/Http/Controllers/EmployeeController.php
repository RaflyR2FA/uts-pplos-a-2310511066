<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();
        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }
        if ($request->has('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }
        if ($request->query('per_page') === 'all') {
            $employees = $query->with('position')->get();
        } else {
            $perPage = $request->input('per_page', 10);
            $employees = $query->with('position')->paginate($perPage);
        }
        return response()->json([
            'message' => 'Employees retrieved successfully.',
            'data' => $employees
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|unique:employees,user_id',
            'position_id' => 'required|exists:positions,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone_number' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $employee = Employee::create($request->all());
        return response()->json([
            'message' => 'Employee created successfully.',
            'data' => $employee
        ], 201);
    }

    public function show(int $id)
    {
        $employee = Employee::with('position')->find($id);
        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found.'
            ], 404);
        }
        return response()->json([
            'message' => 'Employee detail retrieved successfully.',
            'data' => $employee
        ], 200);
    }

    public function update(Request $request, int $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found.'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'position_id' => 'sometimes|exists:positions,id',
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:employees,email,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'hire_date' => 'sometimes|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $employee->update($request->all());
        return response()->json([
            'message' => 'Employee updated successfully.',
            'data' => $employee
        ], 200);
    }

    public function destroy(int $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found.'
            ], 404);
        }
        $employee->delete();
        return response()->json([
            'message' => 'Employee deleted successfully.'
        ], 204);
    }
}
