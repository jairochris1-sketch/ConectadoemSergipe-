<?php

namespace App\Http\Controllers;

use App\Core\SergipeCities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationPreferenceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => ['required', 'string', Rule::in(SergipeCities::getAll())],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $request->session()->put('location_filter', [
            'enabled' => true,
            'city' => $validated['city'],
        ]);

        if ($request->user()) {
            $request->user()->update([
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'last_login_ip' => $request->ip(),
            ]);
        }

        return response()->json([
            'active' => true,
            'city' => $validated['city'],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->session()->forget('location_filter');

        return response()->json(['active' => false]);
    }
}
