<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Course;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = auth()->user();

        if ($user->role !== 'doctor') {
            return response()->json([
                'message' => 'Only doctors can create sessions'
            ], 403);
        }

        $course = Course::find($request->course_id);

        if ($course->doctor_id != $user->id) {
            return response()->json([
                'message' => 'You do not own this course'
            ], 403);
        }

        $session = AttendanceSession::create([
            'course_id'  => $request->course_id,
            'created_by' => $user->id,
            'status'     => 'open',
        ]);

        return response()->json([
            'message' => 'Session created successfully',
            'data' => $session
        ], 201);
    }

    public function close($id)
    {
        $session = AttendanceSession::find($id);

        if (!$session) {
            return response()->json([
                'message' => 'Session not found'
            ], 404);
        }

        $session->status = 'closed';
        $session->save();

        return response()->json([
            'message' => 'Session closed successfully',
            'data' => $session
        ]);
    }
}
