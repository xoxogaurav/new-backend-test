<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'reward',
        'time_estimate',
        'category',
        'difficulty',
        'time_in_seconds',
        'steps',
        'approval_type',
        'is_active',
        'allowed_countries',
        'hourly_limit',
        'daily_limit',
        'one_off',
        'total_submission_limit',
        'daily_submission_limit'
    ];

    protected $casts = [
        'steps' => 'array',
        'reward' => 'decimal:2',
        'time_in_seconds' => 'integer',
        'is_active' => 'boolean',
        'allowed_countries' => 'array',
        'hourly_limit' => 'integer',
        'daily_limit' => 'integer',
        'one_off' => 'boolean',
        'total_submission_limit' => 'integer',
        'daily_submission_limit' => 'integer'
    ];

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isAvailableForUser(User $user): bool
    {
        
         // Check if total submission limit is reached
        if ($this->total_submission_limit > 0) {
            $totalSubmissions = $this->submissions()->count();
            if ($totalSubmissions >= $this->total_submission_limit) {
                return false;
            }
        }
        
        
        // Check if daily submission limit is reached
        if ($this->daily_submission_limit > 0) {
            $dailySubmissions = $this->submissions()
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
    
            if ($dailySubmissions >= $this->daily_submission_limit) {
                return false;
            }
        }
    
        
        // Check if task is one-off and user has already completed it
        if ($this->one_off) {
            $hasCompleted = $this->submissions()
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where('status', 'approved')
                          ->orWhere('status', 'pending')
                          ->orWhere('status', 'rejected');
                })
                ->exists();

            if ($hasCompleted) {
                return false;
            }
        }






        // Check country restriction
        if (!empty($this->allowed_countries) && !in_array($user->country, $this->allowed_countries)) {
            return false;
        }

        // Check hourly limit
        if ($this->hourly_limit > 0) {
            $hourlySubmissions = $this->submissions()
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($hourlySubmissions >= $this->hourly_limit) {
                return false;
            }
        }

        // Check daily limit
        if ($this->daily_limit > 0) {
            $dailySubmissions = $this->submissions()
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            if ($dailySubmissions >= $this->daily_limit) {
                return false;
            }
        }

        return true;
    }
}