<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function getStats()
    {
        try {
            $user = auth()->user();
            
            // Get referred users with their stats
            $referredUsers = $user->referrals()
                ->select([
                    'id', 
                    'name', 
                    'email', 
                    'created_at',
                    'tasks_completed',
                    'balance',
                    'total_withdrawn'
                ])
                ->withSum('transactions as total_earnings', 'amount')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get referral bonus transactions for daily graph data
            $dailyEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'referral_bonus')
                ->where('created_at', '>=', now()->subDays(30)) // Last 30 days
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(amount) as total')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return $this->successResponse([
                'total_referral_earnings' => $user->referral_earnings,
                'referral_share' => $user->referral_share,
                'referral_code' => $user->referral_code,
                'referral_link' => config('app.frontend_url') . '/register?ref=' . $user->referral_code,
                'total_referred_users' => $referredUsers->count(),
                'referred_users' => $referredUsers->map(function ($user) {
                    return [
                        'username' => $user->name,
                        'joined_date' => $user->created_at->toISOString(),
                        'tasks_completed' => $user->tasks_completed,
                        'total_earnings' => $user->total_earnings,
                        'current_balance' => $user->balance,
                        'total_withdrawn' => $user->total_withdrawn
                    ];
                }),
                'daily_earnings' => $dailyEarnings->map(function ($earning) {
                    return [
                        'date' => $earning->date,
                        'amount' => $earning->total
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch referral stats', 'FETCH_ERROR');
        }
    }
}