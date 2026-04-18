<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    public function enroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        if ($request->user()->role != 'student') {
            return response()->json([
                'message' => 'Only students can enroll'
            ], 403);
        }

        $enrollment = Enrollment::firstOrCreate([
            'user_id' => $request->user()->id,
            'course_id' => $request->course_id
        ]);

        return response()->json([
            'message' => 'Enrolled successfully',
            'data' => $enrollment
        ], 201);
    }
}
