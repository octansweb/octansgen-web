<?php

namespace App\OctansGen\Generators;

use App\Models\Automation;
use App\Models\AIInteraction;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class Script
{
    public function generate($prompt, Automation $automation = null)
    {
        // Get response from AI with context from past interactions
        $response = $this->getAIResponse($prompt, $automation);

        // Log the interaction
        $this->logInteraction($prompt, $response, $automation);

        return $response;
    }

    public function getAIResponse($prompt, Automation $automation = null)
    {
        // Retrieve up to the last 20 interactions if available
        $lastInteractions = AIInteraction::where('automation_id', $automation->id ?? null)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->reverse(); // Reverse to maintain chronological order

        // Build messages array with past interactions
        $messages = [
            ['role' => 'system', 'content' => "You are a function that takes in a specific prompt and outputs an instagram reel voiceover script no more than 125 words max. You are to only include the words and no visual directives, headings or anything like. After producing a result, please ensure that it is no more than 175 tokens."]
        ];

        foreach ($lastInteractions as $interaction) {
            // Add user and assistant messages from past interactions
            $messages[] = ['role' => 'user', 'content' => $interaction->prompt];
            $messages[] = ['role' => 'assistant', 'content' => $interaction->response];
        }

        // Add the current prompt
        $messages[] = ['role' => 'user', 'content' => $prompt . "\nPlease understand and make sure to make the script no longer than 125 words and responses to the same prompt must yield unique results."];

        // echo json_encode($messages);

        // Request to OpenAI API
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4-turbo',
            'messages' => $messages,
        ]);

        Log::debug('OpenAI response', ['messages' => $messages, 'response' => $result->choices[0]->message->content]);

        return $result->choices[0]->message->content;
    }

    protected function logInteraction($prompt, $response, Automation $automation = null)
    {
        if (!$automation) {
            return;
        }

        AIInteraction::create([
            'automation_id' => $automation ? $automation->id : null,
            'prompt'        => $prompt,
            'response'      => $response,
        ]);
    }
}
