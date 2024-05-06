<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Image
{
    public function generate($prompt)
    {
        // Use OpenAI to generate an image
        $response = OpenAI::images()->create([
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ]);

        // Assuming the response contains a direct URL to the image
        $imageUrl = $response['data'][0]['url'];

        // Get image content
        $imageContent = Http::get($imageUrl)->body();

        // Define a unique file name
        $fileName = uniqid('image_') . '.png';

        // Define the full path to save the image
        $fullPath = storage_path('app/public/' . $fileName);

        // Save the image content to the file
        file_put_contents($fullPath, $imageContent);

        // Return the path relative to the base storage directory
        return $fullPath;
    }
}
