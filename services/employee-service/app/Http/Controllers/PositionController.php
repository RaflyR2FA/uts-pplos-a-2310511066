<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::all();
        return response()->json([
            'message' => 'Positions retrieved successfully.',
            'data' => $positions
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $position = Position::create($request->all());
        return response()->json([
            'message' => 'Position created successfully.',
            'data' => $position
        ], 201);
    }

    public function show($id)
    {
        $position = Position::find($id);
        if (!$position) {
            return response()->json(['error' => 'Position not found.'], 404);
        }
        return response()->json([
            'message' => 'Position detail retrieved successfully.',
            'data' => $position
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $position = Position::find($id);
        if (!$position) {
            return response()->json(['error' => 'Position not found.'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:positions,name,' . $id,
            'description' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed.',
                'details' => $validator->errors()
            ], 422);
        }
        $position->update($request->all());
        return response()->json([
            'message' => 'Position updated successfully.',
            'data' => $position
        ], 200);
    }

    public function destroy($id)
    {
        $position = Position::find($id);
        if (!$position) {
            return response()->json(['error' => 'Position not found.'], 404);
        }
        if ($position->employees()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete position because it is assigned to one or more employees.'
            ], 409);
        }
        $position->delete();
        return response()->json([
            'message' => 'Position deleted successfully.'
        ], 204);
    }
}
