<?php

namespace App\Http\Controllers;

use App\Http\Requests\RezervationStoreRequest;
use Illuminate\Http\Request;

class RezervationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RezervationStoreRequest $request)
    {
        $userId  = $request->user()->id;
        $eventId = (int) $request->validated('event_id');
        $seatIds = $request->validated('seat_ids');

        return DB::transaction(function () use ($userId, $eventId, $seatIds) {

            // 1) Koltukları kilitle, durumlarını kontrol et
            $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            // Seçilen koltukların hepsi var mı ve reserved mı?
            if ($seats->count() !== count($seatIds)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Some seats not found',
                ], 404);
            }

            // Hepsi aynı etkinliğin mekanında mı? (opsiyonel ama iyi pratik)
            // Event -> venue_id ile seat->venue_id eşleşmesini kontrol etmek istersen:
            // $eventVenueId = Event::findOrFail($eventId)->venue_id;
            // if ($seats->contains(fn($s) => $s->venue_id !== $eventVenueId)) { ... 409 ... }

            // Hepsi reserved olmalı (block edilmeden create engellensin)
            foreach ($seats as $s) {
                if ($s->status !== Seat::STATUS_RESERVED) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Seat {$s->id} is not reserved",
                    ], 409);
                }
            }

            // 2) Toplam tutarı hesapla
            $total = $seats->sum('price');

            // 3) Rezervasyon kaydı
            $rez = Rezervation::create([
                'user_id'      => $userId,
                'event_id'     => $eventId,
                'status'       => 'pending', // confirm ile değişecek
                'total_amount' => $total,
                'expires_at'   => Carbon::now()->addMinutes(15),
            ]);

            // 4) Item’ları ekle
            foreach ($seats as $s) {
                RezervationItem::create([
                    'rezervation_id' => $rez->id,
                    'seat_id'        => $s->id,
                    'price'          => $s->price,
                ]);
            }

            // Not: seats burada SOLD yapılmaz; CONFIRM’de yapılacak.

            // 5) Yanıt
            return response()->json([
                'status'          => 'success',
                'reservation_id'  => $rez->id,
                'total_amount'    => $total,
                'expires_at'      => $rez->expires_at->toISOString(),
                'items_count'     => $seats->count(),
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
