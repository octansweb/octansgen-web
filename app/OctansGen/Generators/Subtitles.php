<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;

class Subtitles
{
    public function generate($speechFilePath)
    {
        // Make a request to the OpenAI API to transcribe the audio file
        $response = OpenAI::audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen($speechFilePath, 'r'),
            'response_format' => 'srt',
        ]);

        // Define a unique file name
        $fileName = uniqid('image_') . '.srt';

        // Define the full path to save the image
        $fullPath = storage_path('app/public/' . $fileName);

        // Save the image content to the file
        file_put_contents($fullPath, $response->text);

        return $fullPath;
    }
}
