<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    public function enroll(Request $request)
    {
        $enrollment = Enrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id
        ]);

        return response()->json($enrollment);
    }
}
