<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;

class Quote
{
    // protected $quotes = [
    //     "The only way to do great work is to love what you do." . PHP_EOL . PHP_EOL . "— Steve Jobs",
    //     "It is never too late to be what you might have been." . PHP_EOL . PHP_EOL . "— George Eliot",
    //     "What you get by achieving your goals is not as important as what you become by achieving your goals." . PHP_EOL . PHP_EOL . "— Zig Ziglar",
    //     "You must be the change you wish to see in the world." . PHP_EOL . PHP_EOL . "— Mahatma Gandhi",
    //     "Success is not final, failure is not fatal: It is the courage to continue that counts." . PHP_EOL . PHP_EOL . "— Winston Churchill",
    // ];

    public function generate($prompt)
    {
        // $randomIndex = array_rand($this->quotes);
        // return $this->quotes[$randomIndex];

        return $this->getAIResponse($prompt);
    }

    public function getAIResponse($prompt)
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4-turbo',
            'temperature' => 1.5,
            'messages' => [
                ['role' => 'system', 'content' => "You are a function that takes in a specific prompt and outputs a unique quote in exacly a particular format (i.e a quote then two line breaks and then long hyphen space author name:
Here are a few examples of interactions:

User: 
Assistant: Give me a quote about success
The only way to do great work is to love what you do.

— Steve Jobs
User: Give me a quote about change
Assistant:
It is never too late to be what you might have been.

— George Eliot"
                ],
                ['role' => 'user', 'content' => 'Give me a quote about success'],
                ['role' => 'assistant', 'content' => "
The only way to do great work is to love what you do.

— Steve Jobs
"],
                ['role' => 'user', 'content' => "
It is never too late to be what you might have been.

— George Eliot
"],
                ['role' => 'user', 'content' => $prompt]
            ],
        ]);

        return $result->choices[0]->message->content;
    }
}
