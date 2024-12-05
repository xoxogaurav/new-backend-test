<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime:Y-m-d\TH:i:s.vP',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.vP'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}