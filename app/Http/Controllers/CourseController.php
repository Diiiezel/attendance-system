<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function create(Request $request)
    {
        $course = Course::create([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return response()->json($course);
    }

    public function getAll()
    {
        return response()->json(Course::all());
    }
}
