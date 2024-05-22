<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OneWordSubtitles
{
    public function generate($speechFilePath)
    {
        // Make a request to the OpenAI API to transcribe the audio file
        $response = OpenAI::audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen($speechFilePath, 'r'),
            'response_format' => 'verbose_json',
            'timestamp_granularities' => ['word'],
        ]);

        $responseArray = $response->toArray();
        Log::debug($responseArray);

        // Generate the SRT file from the response
        $this->generateSrtFile($responseArray);
    }

    protected function generateSrtFile($response)
    {
        // Extracting the words array from the response
        $words = $response['words'];

        // Initializing the .srt file content
        $srtContent = '';
        $subtitleIndex = 1;

        foreach ($words as $index => $wordData) {
            // Preparing the start and end time for the subtitle
            $startTime = $this->convertToSrtTime($wordData['start']);
            $endTime = $this->convertToSrtTime($wordData['end']);

            // Creating a subtitle entry
            $srtContent .= $subtitleIndex++ . "\n";
            $srtContent .= $startTime . ' --> ' . $endTime . "\n";
            $srtContent .= $wordData['word'] . "\n\n";
        }


        // Define a unique file name
        $fileName = uniqid('subtitles_') . '.srt';

        // Define the full path to save the image
        $fullPath = storage_path('app/public/' . $fileName);

        file_put_contents($fullPath, $srtContent);

        Log::debug('SRT file generated successfully!', [
            'file_path' => $fullPath,
        ]);

        // Log the file path
        return storage_path('app/' . $fullPath);
    }

    protected function convertToSrtTime($seconds)
    {
        $milliseconds = round(($seconds - floor($seconds)) * 1000);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = floor($seconds % 60);

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $seconds, $milliseconds);
    }
}
