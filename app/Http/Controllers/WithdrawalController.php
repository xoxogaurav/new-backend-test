<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawalRequest;
use App\Services\WithdrawalService;
use App\Http\Resources\WithdrawalResource;

class WithdrawalController extends Controller
{
    protected $withdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    public function store(WithdrawalRequest $request)
    {
        try {
            $withdrawal = $this->withdrawalService->createWithdrawal(
                auth()->user(),
                $request->validated()
            );

            return $this->successResponse(
                new WithdrawalResource($withdrawal),
                'Withdrawal request submitted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'WITHDRAWAL_ERROR');
        }
    }

    public function index()
    {
        try {
            $withdrawals = $this->withdrawalService->getUserWithdrawals(auth()->user());
            return $this->successResponse(
                WithdrawalResource::collection($withdrawals),
                'Withdrawals retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FETCH_ERROR');
        }
    }
}