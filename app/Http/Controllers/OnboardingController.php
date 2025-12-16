<?php

namespace App\Http\Controllers;

use App\Models\FitnessInterest;
use App\Models\User;
use App\Models\Gym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function index()
    {
        return Inertia::render('Onboarding/Index', [
            'interests' => FitnessInterest::all(),
            'geoapify_key' => config('services.geoapify.key'),
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
            'display_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'experience_level' => ['required', 'string', Rule::in(['Principiante', 'Intermedio', 'Avanzado'])],
            'location_city' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => ['integer', 'exists:fitness_interests,id'],
            'looking_for_interest_id' => ['nullable', 'integer', 'exists:fitness_interests,id'],
            'gym_id' => ['nullable', 'integer', 'exists:gyms,id'],
        ]);

        // 1. Update Basic Info
        $userData = [
            'display_name' => $validated['display_name'],
            'bio' => $validated['bio'],
            'experience_level' => $validated['experience_level'],
            'location_city' => $validated['location_city'] ?? $user->location_city,
            'latitude' => $validated['latitude'] ?? $user->latitude,
            'longitude' => $validated['longitude'] ?? $user->longitude,
            'looking_for_interest_id' => $validated['looking_for_interest_id'],
            'onboarding_completed' => true, // Assuming we add this column or handle it otherwise
        ];

        // Handle Profile Picture Upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profile-photos', 'public');
            $userData['profile_picture_url'] = $path;
        }

        // Ensure username exists
        if (empty($user->username)) {
            $username = \Illuminate\Support\Str::slug($user->name);
            if (empty($username)) $username = 'user';
            $userData['username'] = $username . rand(1000, 9999);
        }

        $user->update($userData);

        // 2. Sync Interests
        if (!empty($validated['interests'])) {
            $user->fitnessInterests()->sync($validated['interests']);
        }

        // 3. Attach Gym (if selected)
        if (!empty($validated['gym_id'])) {
            // Detach previous gyms if we want to limit to 1 during onboarding, or just attach new one
            // For now, let's just attach without detaching (allowing multiple), or sync if singular.
            // Let's assume singular primary gym for onboarding context.
            $user->gyms()->syncWithoutDetaching([$validated['gym_id']]);
        }

        return redirect()->route('feed.index');
    }

    public function skip()
    {
        $user = Auth::user();
        
        $data = ['onboarding_completed' => true];
        
        // Ensure username exists
        if (empty($user->username)) {
            $username = \Illuminate\Support\Str::slug($user->name);
            if (empty($username)) $username = 'user';
            $data['username'] = $username . rand(1000, 9999);
        }

        $user->update($data);
        
        return redirect()->route('feed.index');
    }
}
