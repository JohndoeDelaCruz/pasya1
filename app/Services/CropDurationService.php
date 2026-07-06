<?php

namespace App\Services;

use GuzzleHttp\Client;

class CropDurationService
{
    /**
     * Attempt to fetch a typical days-to-harvest value from Google Custom Search API.
     * Returns integer days or null when not found or not configured.
     */
    public function fetchDaysFromGoogle(string $cropName): ?int
    {
        $apiKey = env('GOOGLE_CUSTOM_SEARCH_API_KEY');
        $cx = env('GOOGLE_CUSTOM_SEARCH_ENGINE_ID');

        if (empty($apiKey) || empty($cx)) {
            return null;
        }

        $client = new Client(['base_uri' => 'https://www.googleapis.com/']);

        try {
            $q = urlencode($cropName . ' days to harvest');
            $res = $client->get("customsearch/v1?key={$apiKey}&cx={$cx}&q={$q}&num=1");
            $body = json_decode($res->getBody()->getContents(), true);
            $snippet = $body['items'][0]['snippet'] ?? null;

            if (!$snippet) {
                return null;
            }

            // Try patterns like "90 days" or "about 90 to 120 days"
            if (preg_match('/(\d{1,3})\s*(?:-|to)\s*(\d{1,3})\s*days/i', $snippet, $m)) {
                $a = (int) $m[1];
                $b = (int) $m[2];
                return (int) round(($a + $b) / 2);
            }

            if (preg_match('/(\d{1,3})\s*days/i', $snippet, $m)) {
                return (int) $m[1];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
