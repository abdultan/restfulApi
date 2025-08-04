<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function rezervations()
    {
        return $this->hasMany(Rezervation::class);
    }
}
