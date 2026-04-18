<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Enrollment;

class AttendanceController extends Controller
{
    public function mark(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:attendance_sessions,id',
            'method' => 'required'
        ]);

        $session = AttendanceSession::find($request->session_id);

        if ($session->status !== 'open') {
            return response()->json([
                'message' => 'Session is closed'
            ], 400);
        }

        $enrolled = Enrollment::where('user_id', $request->user_id)
            ->where('course_id', $session->course_id)
            ->exists();

        if (!$enrolled) {
            return response()->json([
                'message' => 'Student not enrolled in this course'
            ], 403);
        }

        $exists = AttendanceRecord::where('user_id', $request->user_id)
            ->where('attendance_session_id', $request->session_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Student already marked present'
            ], 409);
        }

        $attendance = AttendanceRecord::create([
            'user_id' => $request->user_id,
            'attendance_session_id' => $request->session_id,
            'method' => $request->input('method'),
            'status' => 'present'
        ]);

        return response()->json([
            'message' => 'Attendance marked successfully',
            'data' => $attendance
        ]);
    }

    public function report($session_id)
    {
        $session = AttendanceSession::findOrFail($session_id);

        $students = Enrollment::where('course_id', $session->course_id)
            ->with('user')
            ->get();

        $attended = AttendanceRecord::where('attendance_session_id', $session_id)
            ->pluck('user_id')
            ->toArray();

        $report = [];

        foreach ($students as $student) {
            $user = $student->user;

            $report[] = [
                'university_code' => $user->university_code,
                'full_name' => $user->name,
                'status' => in_array($user->id, $attended) ? 'present' : 'absent'
            ];
        }

        return response()->json($report);
    }
}
