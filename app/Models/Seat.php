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
        'rezerved_by',
        'rezerved_until',
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
        return $this->hasOne(Ticket::class);
    }
}
