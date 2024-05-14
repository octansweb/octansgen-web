<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;

class InstagramDescription
{
    public function generate($script)
    {
        $messages = [
            ['role' => 'system', 'content' => "You receive a voiceover script and generate a unique Instagram description under 2200 characters. Only provide the Instagram description, including up to five of the most relevant and viral hashtags related to the script."],
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
