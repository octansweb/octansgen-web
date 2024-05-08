<?php

namespace App\OctansGen\Generators;

use Illuminate\Support\Facades\Http;

class Audio
{
    public function generate($text)
    {
        // Make a request to the OpenAI API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/audio/speech', [
            'model' => 'tts-1',
            'input' => $text,
            'voice' => 'onyx',
        ]);

        // Assuming the response contains the audio file content
        $audioContent = $response->body();

        // Define a unique file name
        $fileName = uniqid('speech_') . '.mp3';

        // Define the full path to save the audio file
        $fullPath = storage_path('app/public/' . $fileName);

        // Save the audio content to the file
        file_put_contents($fullPath, $audioContent);

        // Return the path relative to the base storage directory
        return $fullPath;
    }
}
