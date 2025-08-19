<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)

class Event extends Model
{
    use HasFactory;

<<<<<<< HEAD
    protected $guarded = [];
=======
    protected $fillable = ['name', 'description', 'venue_id', 'start_date', 'end_date', 'status'];

	protected $casts = [
		'start_date' => 'datetime',
		'end_date' => 'datetime',
	];
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function rezervations()
    {
        return $this->hasMany(Rezervation::class);
    }
<<<<<<< HEAD
=======
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    /** QUERY SCOPES */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('start_date', '<', now());
    }
    
    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('start_date', [$start, $end]);
    }

    public function scopeSearch($query, $term){
        if (!$term) return $query;
        return $query->where(fn($w) =>
            $w->where('name','like',"%$term%")
              ->orWhere('description','like',"%$term%"));
    }
    public function scopeByVenue($q, $venueId)
    {
        return $q->when($venueId, fn($qq) => $qq->where('venue_id', $venueId));
    }
    public function getIsPastAttribute()
    {
        return $this->end_date instanceof Carbon
            ? $this->end_date->isPast()
            : Carbon::parse($this->end_date)->isPast();
    }

    protected static function booted()
    {
        static::saving(function (Event $event) {
            if (!$event->venue_id || !$event->start_date || !$event->end_date) {
                return;
            }

            try {
                $startAt = $event->start_date instanceof Carbon ? $event->start_date : Carbon::parse($event->start_date);
                $endAt   = $event->end_date   instanceof Carbon ? $event->end_date   : Carbon::parse($event->end_date);
            } catch (\Throwable $e) {
                return; // invalid dates will be handled by validation elsewhere if present
            }

            if ($endAt->lessThanOrEqualTo($startAt)) {
                throw ValidationException::withMessages([
                    'end_date' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.'
                ]);
            }

            $overlaps = static::query()
                ->where('venue_id', $event->venue_id)
                ->when($event->exists, fn ($q) => $q->where('id', '!=', $event->id))
                ->where('start_date', '<', $endAt)
                ->where('end_date', '>', $startAt)
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages([
                    'start_date' => "Aynı venue'da bu tarih aralığıyla çakışan bir etkinlik mevcut."
                ]);
            }
        });
    }
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
}
