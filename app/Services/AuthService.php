<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'country' => $data['country'],
            'balance' => 0,
            'pending_earnings' => 0,
            'total_withdrawn' => 0,
            'tasks_completed' => 0,
            'success_rate' => 0,
            'average_rating' => 0,
            'referral_earnings' => 0,
        ];

        // Add referred_by if present
        if (isset($data['referred_by'])) {
            $userData['referred_by'] = $data['referred_by'];
        }

        $user = User::create($userData);

        // Generate JWT token
        $token = auth()->login($user);

        return [
            'token' => $token,
            'user' => $this->formatUserData($user)
        ];
    }

    public function login(array $credentials)
    {
        if (!$token = auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'token' => $token,
            'user' => $this->formatUserData(auth()->user())
        ];
    }

    private function formatUserData(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'country' => $user->country,
            'is_admin' => $user->is_admin,
            'referral_code' => $user->referral_code,
            'referral_link' => config('app.frontend_url') . '/register?ref=' . $user->referral_code,
            'referral_share' => $user->referral_share,
            'referral_earnings' => $user->referral_earnings,
        ];
    }
}