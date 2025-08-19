<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = [];

<<<<<<< HEAD
=======
    public const STATUS_ACTIVE     = 'active';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_USED       = 'used';
    public const STATUS_TRANSFERRED= 'transferred';
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    public function rezervation(){
        return $this->belongsTo(Rezervation::class);
    }

    public function seat(){
        return $this->belongsTo(Seat::class);
    }
<<<<<<< HEAD
=======
    public function scopeOwnedBy($q, int $userId)
    {
        return $q->whereHas('rezervation', fn($qr) => $qr->where('user_id', $userId));
    }
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
}
