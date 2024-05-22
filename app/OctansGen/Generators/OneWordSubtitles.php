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

        return $this->generateSrtFile($responseArray);
    }

    protected function generateSrtFile($response)
    {
        // Extracting the words array from the response
        $words = $response['words'];

        // Initializing the .srt file content
        $srtContent = '';
        $subtitleIndex = 1;
        $currentEntry = [];
        $previousEndTime = null;

        foreach ($words as $index => $wordData) {
            $startTime = $this->convertToSrtTime($wordData['start']);
            $endTime = isset($words[$index + 1]) ? $this->convertToSrtTime($words[$index + 1]['start']) : $this->convertToSrtTime($wordData['end']);

            // Check if current word should be part of the current entry
            if (!empty($currentEntry) && $currentEntry['startTime'] == $startTime) {
                $currentEntry['text'] .= ' ' . strtoupper($wordData['word']);
                $currentEntry['endTime'] = $endTime;
            } else {
                // If there's a previous entry, add it to the srtContent
                if (!empty($currentEntry)) {
                    $srtContent .= $subtitleIndex++ . "\n";
                    $srtContent .= $currentEntry['startTime'] . ' --> ' . $currentEntry['endTime'] . "\n";
                    $srtContent .= $currentEntry['text'] . "\n\n";
                }

                // Start a new entry
                $currentEntry = [
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'text' => strtoupper($wordData['word'])
                ];
            }
        }

        // Add the last entry if exists
        if (!empty($currentEntry)) {
            $srtContent .= $subtitleIndex++ . "\n";
            $srtContent .= $currentEntry['startTime'] . ' --> ' . $currentEntry['endTime'] . "\n";
            $srtContent .= $currentEntry['text'] . "\n\n";
        }

        // Define a unique file name
        $fileName = uniqid('subtitles_') . '.srt';

        // Define the full path to save the subtitle file
        $fullPath = storage_path('app/public/' . $fileName);

        file_put_contents($fullPath, $srtContent);

        Log::debug('SRT file generated successfully!', [
            'file_path' => $fullPath,
        ]);

        // Log the file path
        return $fullPath;
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
