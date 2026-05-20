<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FacePlusPlusService;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Enrollment;

class FaceController extends Controller
{
    public function register(Request $request, FacePlusPlusService $faceService)
    {
        $request->validate([
            'image' => 'required|image'
        ]);

        $user = $request->user();

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can register face'
            ], 403);
        }

        $file = $request->file('image');
        $path = $file->getRealPath();

        $result = $faceService->detect($path);

        if (!isset($result['faces'][0]['face_token'])) {
            return response()->json([
                'message' => 'No face detected'
            ], 422);
        }

        $students = User::where('role', 'student')
            ->whereNotNull('face_token')
            ->where('id', '!=', $user->id)
            ->get();

        foreach ($students as $student) {
            $compareResult = $faceService->compareFaceToken(
                $path,
                $student->face_token
            );

            if (isset($compareResult['confidence']) && $compareResult['confidence'] >= 80) {
                return response()->json([
                    'message' => 'This face is already registered to another student',
                    'matched_student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'university_code' => $student->university_code,
                    ],
                    'confidence' => $compareResult['confidence']
                ], 409);
            }
        }

        $user->face_token = $result['faces'][0]['face_token'];
        $user->save();

        return response()->json([
            'message' => 'Face registered successfully',
            'face_token' => $user->face_token
        ]);
    }

    public function verifyAndMark(Request $request, FacePlusPlusService $faceService)
    {
        $request->validate([
            'image' => 'required|image',
            'session_id' => 'required|exists:attendance_sessions,id'
        ]);

        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Only doctors can verify and mark attendance'
            ], 403);
        }

        $file = $request->file('image');
        $path = $file->getRealPath();

        $students = User::where('role', 'student')
            ->whereNotNull('face_token')
            ->get();

        foreach ($students as $student) {
            $result = $faceService->compareFaceToken(
                $path,
                $student->face_token
            );

            if (isset($result['confidence']) && $result['confidence'] >= 80) {
                $session = AttendanceSession::find($request->session_id);

                if ($session->status !== 'open') {
                    return response()->json([
                        'matched' => true,
                        'message' => 'Session is closed',
                        'student' => [
                            'id' => $student->id,
                            'name' => $student->name,
                            'email' => $student->email,
                            'university_code' => $student->university_code,
                        ],
                        'confidence' => $result['confidence']
                    ], 400);
                }

                $enrolled = Enrollment::where('user_id', $student->id)
                    ->where('course_id', $session->course_id)
                    ->exists();

                if (!$enrolled) {
                    return response()->json([
                        'matched' => true,
                        'message' => 'Student not enrolled in this course',
                        'student' => [
                            'id' => $student->id,
                            'name' => $student->name,
                            'email' => $student->email,
                            'university_code' => $student->university_code,
                        ],
                        'confidence' => $result['confidence']
                    ], 403);
                }

                $exists = AttendanceRecord::where('user_id', $student->id)
                    ->where('attendance_session_id', $session->id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'matched' => true,
                        'message' => 'Student already marked present',
                        'student' => [
                            'id' => $student->id,
                            'name' => $student->name,
                            'email' => $student->email,
                            'university_code' => $student->university_code,
                        ],
                        'confidence' => $result['confidence']
                    ], 409);
                }

                $attendance = AttendanceRecord::create([
                    'user_id' => $student->id,
                    'attendance_session_id' => $session->id,
                    'method' => 'face',
                    'status' => 'present'
                ]);

                return response()->json([
                    'matched' => true,
                    'message' => 'Attendance marked successfully by face',
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'university_code' => $student->university_code,
                    ],
                    'confidence' => $result['confidence'],
                    'attendance' => $attendance
                ], 201);
            }
        }

        return response()->json([
            'matched' => false,
            'message' => 'No matching student found'
        ], 404);
    }
}
