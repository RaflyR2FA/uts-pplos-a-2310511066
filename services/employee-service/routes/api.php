<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::post('attendances/clock-in', [AttendanceController::class, 'clockIn']);
    Route::put('attendances/clock-out/{id}', [AttendanceController::class, 'clockOut']);
    Route::apiResource('leaves', LeaveRequestController::class)->except(['update', 'destroy']);
    Route::patch('leaves/{id}/approval', [LeaveRequestController::class, 'updateApproval']);
});
