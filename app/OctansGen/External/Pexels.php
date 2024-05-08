<?php

namespace App\OctansGen\External;

use Illuminate\Support\Facades\Http;


class Pexels
{
    public function search($query)
    {
        // Retrieve the API key from environment variables
        $apiKey = env('PEXELS_API_KEY');

        // URL for the Pexels API
        $url = "https://api.pexels.com/videos/search";

        // Send a GET request to the Pexels API
        $response = Http::withHeaders([
            'Authorization' => $apiKey
        ])->get($url, [
            'query' => $query,
            'orientation' => 'portrait'
        ]);

        // Return the response body as an array
        return $response->json();
    }
}
