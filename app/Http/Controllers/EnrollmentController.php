<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Course;

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

    public function myCourses(Request $request)
    {
        if ($request->user()->role != 'student') {
            return response()->json([
                'message' => 'Only students can view my courses'
            ], 403);
        }

        $courses = Course::with('doctor')
            ->whereHas('enrollments', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->get();

        return response()->json($courses);
    }

    public function availableCourses(Request $request)
    {
        if ($request->user()->role != 'student') {
            return response()->json([
                'message' => 'Only students can view available courses'
            ], 403);
        }

        $enrolledCourseIds = Enrollment::where('user_id', $request->user()->id)
            ->pluck('course_id');

        $courses = Course::with('doctor')
            ->whereNotIn('id', $enrolledCourseIds)
            ->get();

        return response()->json($courses);
    }
}
