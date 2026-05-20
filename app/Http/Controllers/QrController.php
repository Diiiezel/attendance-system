<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Enrollment;

class QrController extends Controller
{
    public function generate(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can generate QR'
            ], 403);
        }

        $data = json_encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'university_code' => $user->university_code,
        ]);

        $qrUrl = 'https://quickchart.io/qr?size=300&text=' . urlencode($data);

        return response()->json([
            'message' => 'QR generated successfully',
            'qr_url' => $qrUrl,
            'qr_data' => $data
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required',
            'session_id' => 'required|exists:attendance_sessions,id',
        ]);

        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Only doctors can scan QR'
            ], 403);
        }

        $decoded = json_decode($request->qr_data, true);

        if (!$decoded || !isset($decoded['id'])) {
            return response()->json([
                'message' => 'Invalid QR data'
            ], 422);
        }

        $student = User::where('id', $decoded['id'])
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        $session = AttendanceSession::find($request->session_id);

        if ($session->status !== 'open') {
            return response()->json([
                'message' => 'Session is closed'
            ], 400);
        }

        $enrolled = Enrollment::where('user_id', $student->id)
            ->where('course_id', $session->course_id)
            ->exists();

        if (!$enrolled) {
            return response()->json([
                'message' => 'Student not enrolled in this course'
            ], 403);
        }

        $exists = AttendanceRecord::where('user_id', $student->id)
            ->where('attendance_session_id', $session->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Student already marked present',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'university_code' => $student->university_code,
                ]
            ], 409);
        }

        $attendance = AttendanceRecord::create([
            'user_id' => $student->id,
            'attendance_session_id' => $session->id,
            'method' => 'qr',
            'status' => 'present'
        ]);

        return response()->json([
            'message' => 'Attendance marked successfully by QR',
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'university_code' => $student->university_code,
            ],
            'attendance' => $attendance
        ], 201);
    }
}
