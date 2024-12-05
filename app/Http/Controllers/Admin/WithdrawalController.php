<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    protected $withdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    public function pending()
    {
        try {
            $withdrawals = Withdrawal::with('user:id,name,email')
                ->where('status', 'pending')
                ->get();

            return $this->successResponse($withdrawals);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pending withdrawals', 'FETCH_ERROR');
        }
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected'
            ]);

            $withdrawal = $this->withdrawalService->processWithdrawal(
                $withdrawal,
                $request->status
            );

            return $this->successResponse($withdrawal, 'Withdrawal request processed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PROCESS_ERROR');
        }
    }
}