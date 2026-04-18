<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    // عرض كل الكورسات
    public function getAll()
    {
        $courses = Course::with('doctor')->get();

        return response()->json($courses);
    }

    // إنشاء كورس بواسطة دكتور
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:courses,code',
            'semester' => 'nullable',
            'level' => 'nullable'
        ]);

        if ($request->user()->role != 'doctor') {
            return response()->json([
                'message' => 'Only doctors can create courses'
            ], 403);
        }

        $course = Course::create([
            'name' => $request->name,
            'code' => $request->code,
            'doctor_id' => $request->user()->id,
            'semester' => $request->semester,
            'level' => $request->level
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }
}
