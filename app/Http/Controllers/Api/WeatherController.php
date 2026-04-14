<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleWeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(private readonly GoogleWeatherService $weatherService)
    {
    }

    public function current(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'municipality' => ['required', 'string', 'max:100'],
        ]);

        $result = $this->weatherService->getCurrentConditions($validated['municipality']);

        if (($result['success'] ?? false) === true) {
            return response()->json($result);
        }

        $statusCode = match ($result['error_code'] ?? null) {
            'not_configured' => 503,
            'missing_coordinates' => 404,
            default => 502,
        };

        return response()->json($result, $statusCode);
    }
}