<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            // Handle authentication failure (e.g., user denied access)
            return response()->json(['error' => 'Authentication failed.'], 401);
        }

        // Check if the user already exists in  database
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // Create a new user in your database if they don't exist
            $newUser = User::create([
                'firstname' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
            ]);

            Auth::login($newUser);

            return response()->json(['message' => 'Successfully logged in with Google!']);
        } else {
            Auth::login($user);
            // Optionally, you can return a response with the access token or redirect the user to a specific URL after successful login
            return response()->json(['message' => 'Successfully logged in with Google!']);
        }
    }
}
