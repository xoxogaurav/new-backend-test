<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponse;

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        try {
            $transactions = Transaction::with('task')
                ->where('user_id', auth()->id())
                ->orderByDesc('created_at')
                ->get();

            return $this->successResponse($transactions);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch transactions', 'FETCH_ERROR');
        }
    }

    public function withdraw(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1'
            ]);

            $transaction = $this->transactionService->withdraw($request->amount);
            return $this->successResponse([
                'transaction' => $transaction
            ], 'Withdrawal request submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'WITHDRAWAL_ERROR');
        }
    }
}