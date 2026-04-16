<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\AttendanceController;


Route::get('/sessions/{id}/report', [AttendanceController::class, 'report']);
Route::post('/attendance', [AttendanceController::class, 'mark']);
Route::post('/sessions', [AttendanceSessionController::class, 'create']);
Route::post('/sessions/{id}/close', [AttendanceSessionController::class, 'close']);
Route::post('/enroll', [EnrollmentController::class, 'enroll']);
Route::post('/courses', [CourseController::class, 'create']);
Route::get('/courses', [CourseController::class, 'getAll']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test', function () {
    return response()->json(['message' => 'API working']);
});
