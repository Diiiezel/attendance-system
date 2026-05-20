<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FaceController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return response()->json([
        'message' => 'API working'
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/courses', [CourseController::class, 'getAll']);
    Route::get('/my-qr', [QrController::class, 'generate']);
});

/*
|--------------------------------------------------------------------------
| Doctor Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:doctor'])->group(function () {

    Route::post('/courses', [CourseController::class, 'create']);

    Route::post('/sessions', [AttendanceSessionController::class, 'create']);
    Route::post('/sessions/{id}/close', [AttendanceSessionController::class, 'close']);

    Route::get('/sessions/{id}/report', [AttendanceController::class, 'report']);

    Route::get('/courses/{id}/report', [ReportController::class, 'courseReport']);

    Route::get('/courses/{id}/export', [ReportController::class, 'exportCourseReport']);

    Route::post('/face/verify', [FaceController::class, 'verify']);

    Route::post('/face/verify-and-mark', [FaceController::class, 'verifyAndMark']);

    Route::post('/qr/scan', [QrController::class, 'scan']);
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {

    Route::post('/face/register', [FaceController::class, 'register']);

    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);

    Route::get('/available-courses', [EnrollmentController::class, 'availableCourses']);

    Route::post('/enroll', [EnrollmentController::class, 'enroll']);

    Route::post('/attendance', [AttendanceController::class, 'mark']);

    Route::get('/students/{id}/report', [ReportController::class, 'studentReport']);

    Route::get('/my-report', [ReportController::class, 'myReport']);


});
