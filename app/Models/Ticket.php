<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = [];

    public const STATUS_ACTIVE      = 'active';
    public const STATUS_CANCELLED   = 'cancelled';
    public const STATUS_USED        = 'used';
    public const STATUS_TRANSFERRED = 'transferred';

    public function rezervation(){
        return $this->belongsTo(Rezervation::class);
    }

    public function seat(){
        return $this->belongsTo(Seat::class);
    }

    public function scopeOwnedBy($q, int $userId)
    {
        return $q->whereHas('rezervation', fn($qr) => $qr->where('user_id', $userId));
    }
}
