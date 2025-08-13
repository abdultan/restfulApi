<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RezervationItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function rezervation(){
        return $this->belongsTo(Rezervation::class);
    }

    public function seat(){
        return $this->belongsTo(Seat::class);
    }

}
