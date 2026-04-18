<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacePlusPlusService
{
    private $key;
    private $secret;
    private $baseUrl;

    public function __construct()
    {
        $this->key = env('FACEPP_API_KEY');
        $this->secret = env('FACEPP_API_SECRET');
        $this->baseUrl = env('FACEPP_BASE_URL');
    }

    public function detect($imagePath)
    {
        return Http::asMultipart()->post($this->baseUrl . '/facepp/v3/detect', [
            [
                'name' => 'api_key',
                'contents' => $this->key
            ],
            [
                'name' => 'api_secret',
                'contents' => $this->secret
            ],
            [
                'name' => 'image_file',
                'contents' => fopen($imagePath, 'r')
            ]
        ])->json();
    }

    public function compare($image1, $image2)
    {
        return Http::asMultipart()->post($this->baseUrl . '/facepp/v3/compare', [
            [
                'name' => 'api_key',
                'contents' => $this->key
            ],
            [
                'name' => 'api_secret',
                'contents' => $this->secret
            ],
            [
                'name' => 'image_file1',
                'contents' => fopen($image1, 'r')
            ],
            [
                'name' => 'image_file2',
                'contents' => fopen($image2, 'r')
            ]
        ])->json();
    }

    public function compareFaceToken($imagePath, $faceToken)
    {
        return Http::asMultipart()->post($this->baseUrl . '/facepp/v3/compare', [
            [
                'name' => 'api_key',
                'contents' => $this->key
            ],
            [
                'name' => 'api_secret',
                'contents' => $this->secret
            ],
            [
                'name' => 'image_file1',
                'contents' => fopen($imagePath, 'r')
            ],
            [
                'name' => 'face_token2',
                'contents' => $faceToken
            ]
        ])->json();
    }
}
