<?php

namespace App\OctansGen\Generators;

use OpenAI\Laravel\Facades\OpenAI;


class ImagePrompt
{
    public function generate($partialScript, $fullScript)
    {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are an AI specialized in generating detailed image prompts for captivating visual scenes. You will be a given a partial script (which is the voiceover part of the video for which the image is need and the full script, so you can understand the context of the video. First imagine a very detailed and very attention capturing scene. Then, based on it create a prompt for visually captivating scenes that can be used as b-roll in short videos.'
                ],

                [
                    'role' => 'user',
                    'content' => "Partial Script: Picture yourself as a seed buried in darkness, feeling lost and alone. Yet, with each passing moment, you're silently growing stronger, pushing through the soil toward the light. This is your time of growth. The challenges and pressures you face are transforming you, nurturing your potential.\n\nFull Script: Picture yourself as a seed buried in darkness, feeling lost and alone. Yet, with each passing moment, you're silently growing stronger, pushing through the soil toward the light. This is your time of growth. The challenges and pressures you face are transforming you, nurturing your potential. Just as the seed breaks through into the sunlight, flourishing into a beautiful flower, you too are on the verge of a breakthrough. Never underestimate the power of perseverance. Keep pushing, keep growing. Soon, you'll bloom brilliantly, a testament to the beauty that comes from overcoming obstacles. Remember, even in darkness, growth is happening. Stay strong, your moment in the sun is coming.\nImage Prompt:"
                ],

                [
                    'role' => 'assistant',
                    'content' => 'Create a detailed image of a seed buried in dark, rich soil. Above the soil, show a faint light breaking through, symbolizing hope and growth. As you move upwards, depict the seedling pushing through the soil with tiny roots spreading out. The surroundings should include subtle signs of life, such as small insects or dewdrops on the soil. Above ground, show the seedling beginning to sprout, reaching towards the light. The background can include a soft, warm light from the rising sun, casting a gentle glow. This scene should evoke a sense of silent strength, perseverance, and the beauty of growth even in dark times.'
                ],

                [
                    'role' => 'user',
                    'content' => "Partial Script: $partialScript\n\nFull Script: $fullScript\n\nImage Prompt:"
                ]

            ];
    
            // Request to OpenAI API
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4-turbo',
                'messages' => $messages,
            ]);
    
            return $result->choices[0]->message->content;
    }
}
