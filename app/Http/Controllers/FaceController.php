<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FacePlusPlusService;

class FaceController extends Controller
{
    public function register(Request $request, FacePlusPlusService $faceService)
    {
        $request->validate([
            'image' => 'required|image'
        ]);

        $user = $request->user();

        $file = $request->file('image');
        $path = $file->getRealPath();

        $result = $faceService->detect($path);

        if (!isset($result['faces'][0]['face_token'])) {
            return response()->json([
                'message' => 'No face detected'
            ], 422);
        }

        $user->face_token = $result['faces'][0]['face_token'];
        $user->save();

        return response()->json([
            'message' => 'Face registered successfully',
            'face_token' => $user->face_token
        ]);
    }

    public function verify(Request $request, FacePlusPlusService $faceService)
    {
        $request->validate([
            'image' => 'required|image'
        ]);

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
                return response()->json([
                    'matched' => true,
                    'student' => $student,
                    'confidence' => $result['confidence']
                ]);
            }
        }

        return response()->json([
            'matched' => false,
            'message' => 'No matching student found'
        ]);
    }
}
