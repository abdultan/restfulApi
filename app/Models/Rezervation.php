<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rezervation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function event(){
        return $this->belongsTo(Event::class);
    }

    public function tickets(){
        return $this->hasMany(Ticket::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function items(){
        return $this->hasMany(RezervationItem::class);
    }
}
