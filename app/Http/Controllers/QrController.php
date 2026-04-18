<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QrController extends Controller
{
    public function generate(Request $request)
    {
        $user = $request->user();

        $data = json_encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'university_code' => $user->university_code,
        ]);

        $qrUrl = 'https://quickchart.io/qr?size=300&text=' . urlencode($data);

        return response()->json([
            'message' => 'QR generated successfully',
            'qr_url' => $qrUrl
        ]);
    }
}
