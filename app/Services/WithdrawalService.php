<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function createWithdrawal(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            if ($user->balance < $data['amount']) {
                throw ValidationException::withMessages([
                    'amount' => ['Insufficient balance']
                ]);
            }

            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_details' => $data['payment_details'],
                'status' => 'pending'
            ]);

            // Create transaction record
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => 'withdrawal',
                'status' => 'pending'
            ]);

            // Update user balance
            $user->balance -= $data['amount'];
            $user->total_withdrawn += $data['amount'];
            $user->save();

            return $withdrawal;
        });
    }

    public function getUserWithdrawals(User $user)
    {
        return Withdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function processWithdrawal(Withdrawal $withdrawal, string $status)
    {
        return DB::transaction(function () use ($withdrawal, $status) {
            $withdrawal->status = $status;
            $withdrawal->processed_at = now();
            $withdrawal->save();

            $user = $withdrawal->user;
            $transaction = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('amount', $withdrawal->amount)
                ->where('status', 'pending')
                ->first();

            if ($transaction) {
                $transaction->status = $status === 'approved' ? 'completed' : 'failed';
                $transaction->save();

                // Process referral bonus if withdrawal is approved
                if ($status === 'approved' && $user->referrer) {
                    $this->processReferralBonus($user->referrer, $withdrawal);
                }
            }

            // Create notification for withdrawal user
            $message = $status === 'approved' 
                ? "Your withdrawal request for \${$withdrawal->amount} has been approved."
                : "Your withdrawal request for \${$withdrawal->amount} has been rejected.";

            Notification::create([
                'user_id' => $withdrawal->user_id,
                'title' => 'Withdrawal Update',
                'message' => $message,
                'type' => $status === 'approved' ? 'success' : 'error',
                'is_read' => false
            ]);

            return $withdrawal;
        });
    }

    protected function processReferralBonus(User $referrer, Withdrawal $withdrawal)
    {
        // Use referrer's custom share percentage
        $bonusAmount = $withdrawal->amount * ($referrer->referral_share / 100);

        // Update referrer's earnings
        $referrer->referral_earnings += $bonusAmount;
        $referrer->balance += $bonusAmount;
        $referrer->save();

        // Create transaction for referral bonus
        Transaction::create([
            'user_id' => $referrer->id,
            'amount' => $bonusAmount,
            'type' => 'referral_bonus',
            'status' => 'completed'
        ]);

        // Notify referrer about the bonus
        Notification::create([
            'user_id' => $referrer->id,
            'title' => 'Referral Bonus Received',
            'message' => "You received a \${$bonusAmount} referral bonus ({$referrer->referral_share}%) from {$withdrawal->user->name}'s withdrawal.",
            'type' => 'success',
            'is_read' => false
        ]);
    }
}