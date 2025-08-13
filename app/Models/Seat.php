<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function venue(){
        return $this->belongsTo(Venue::class);
    }

    public function tickets(){
        return $this->hasOne(Ticket::class);
    }
}
