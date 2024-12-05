<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function withdraw($amount)
    {
        $user = auth()->user();

        if ($user->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => ['Insufficient balance'],
            ]);
        }

        return DB::transaction(function () use ($user, $amount) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'withdrawal',
                'status' => 'pending'
            ]);

            $user->balance -= $amount;
            $user->save();

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Withdrawal Requested',
                'message' => "Your withdrawal request for \${$amount} is being processed.",
                'type' => 'info',
                'is_read' => false
            ]);

            return $transaction;
        });
    }
}