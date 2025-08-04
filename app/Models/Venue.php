<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address', 
        'capacity'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
