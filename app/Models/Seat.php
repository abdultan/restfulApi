<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;
    protected $fillable = [
        'venue_id',
        'section',
        'row',
        'number',
        'status',
        'price',
<<<<<<< HEAD
        'rezerved_by',
        'rezerved_until',
=======
        'reserved_by',
        'reserved_until',
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    ];
    protected $casts = [
        'reserved_until' => 'datetime',
    ];
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_SOLD = 'sold';
    public function venue(){
        return $this->belongsTo(Venue::class);
    }

    public function tickets(){
<<<<<<< HEAD
        return $this->hasOne(Ticket::class);
=======
        return $this->hasMany(Ticket::class);
    }
    public function activeTicket()
    {
        return $this->hasOne(Ticket::class)->where('status', Ticket::STATUS_ACTIVE)->latestOfMany();
    }   
    public function rezervationItems()
    {
        return $this->hasMany(\App\Models\RezervationItem::class, 'seat_id', 'id');
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }
}
