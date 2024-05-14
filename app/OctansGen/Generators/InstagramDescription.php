<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;

class InstagramDescription
{
    public function generate($script)
    {
        $messages = [
            ['role' => 'system', 'content' => "You are a function that takes in a voiceover script and outputs a unique Instagram description less than 2200 characters. You are to only output the Instagram description and no other supporting text."],
            ['role' => 'user', 'content' => $script],
        ];

        // Request to OpenAI API
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4-turbo',
            'messages' => $messages,
        ]);

        return $result->choices[0]->message->content;
    }
}
