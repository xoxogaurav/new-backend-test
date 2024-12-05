<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select([
            'id', 'name', 'email', 'balance', 'pending_earnings',
            'total_withdrawn', 'tasks_completed', 'success_rate',
            'average_rating', 'created_at'
        ])->get();

        return $this->successResponse($users);
    }
    
    
    public function getPendingGovtIdUsers()
    {
        try {
            // Fetch users with pending governmentIdStatus
            $users = User::where('governmentIdStatus', 'pending')
                ->select([
                    'id', 'name', 'email', 'governmentIdUrl', 'created_at'
                ])
                ->get();
    
            return $this->successResponse($users, 'Fetched pending government ID users successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch pending government ID users', 'FETCH_ERROR');
        }
    }
    
    
    public function approveGovtId(Request $request, $userId)
    {
        try {
            // Validate the request input
            $request->validate([
                'status' => 'required|in:approved,rejected'
            ]);
    
            // Find the user by ID
            $user = User::findOrFail($userId);
    
            // Update the government ID status
            $user->update([
                'governmentIdStatus' => $request->status
            ]);
    
            $message = $request->status === 'approved'
                ? 'Government ID approved successfully.'
                : 'Government ID rejected successfully.';
    
            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'governmentIdStatus' => $user->status
                ]
            ], $message);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update government ID status', 'UPDATE_ERROR');
        }
    }




}