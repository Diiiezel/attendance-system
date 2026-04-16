<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSession;

class AttendanceSessionController extends Controller
{
    // فتح session
    public function create(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        // check لو في session مفتوحة
        $existing = AttendanceSession::where('course_id', $request->course_id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Session already open'
            ], 409);
        }

        $session = AttendanceSession::create([
            'course_id' => $request->course_id,
            'status' => 'open'
        ]);

        return response()->json([
            'message' => 'Session created',
            'data' => $session
        ]);
    }

    // قفل session
    public function close($id)
    {
        $session = AttendanceSession::find($id);

        if (!$session) {
            return response()->json([
                'message' => 'Session not found'
            ], 404);
        }

        if ($session->status === 'closed') {
            return response()->json([
                'message' => 'Session already closed'
            ], 400);
        }

        $session->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Session closed'
        ]);
    }
}
