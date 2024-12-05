<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'balance',
        'pending_earnings',
        'total_withdrawn',
        'tasks_completed',
        'success_rate',
        'average_rating',
        'country',
        'age',
        'phone_number',
        'bio',
        'profile_picture',
        'timezone',
        'language',
        'email_notifications',
        'governmentIdUrl',
        'governmentIdStatus',
        'referral_code',
        'referred_by',
        'referral_earnings',
        'referral_share',
        'fcm_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'balance' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'success_rate' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'email_notifications' => 'boolean',
        'referral_earnings' => 'decimal:2',
        'referral_share' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            $user->referral_code = $user->generateUniqueReferralCode();
            $user->referral_share = 10.00; // Default 10%
        });
    }

    public function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function tasks()
    {
        return $this->hasMany(TaskSubmission::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }
}