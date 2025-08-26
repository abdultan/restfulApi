<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketTransferRequest;
use App\Http\Requests\TicketCancelRequest;
use App\Models\Ticket;
use App\Models\Rezervation;
use App\Models\RezervationItem;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $q = Ticket::with(['seat', 'rezervation.event'])
            ->whereHas('rezervation', fn($qr) => $qr->where('user_id', $userId));

        if ($request->filled('status')) {
            $q->where('status', $request->string('status')); // active|cancelled|transferred...
        }

        if ($request->filled('event_id')) {
            $q->whereHas('rezervation', fn($qr) => $qr->where('event_id', (int)$request->event_id));
        }

        $tickets = $q->latest()->paginate(10);
        return $this->successResponse($tickets, 'Tickets retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        return $this->notImplementedResponse();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $ticket = Ticket::with(['seat', 'rezervation.event'])
            ->where('id', $id)
            ->whereHas('rezervation', fn($qr) => $qr->where('user_id', $userId))
            ->first();

        if (!$ticket) {
            return $this->notFoundResponse('Ticket not found');
        }

        return $this->successResponse($ticket, 'Ticket retrieved successfully');
    }
    public function transfer(TicketTransferRequest $request, int $id): JsonResponse
    {
        $actorId = $request->user()->id;               // transferi yapan
        $toUserId = (int) $request->validated('to_user_id');
        $note     = $request->validated('note') ?? null;

        $updated = DB::transaction(function () use ($id, $actorId, $toUserId, $note) {

            // 1) Bileti kilitleyerek (race condition önlemi) oku
            $ticket = Ticket::with(['rezervation.event', 'seat'])
                ->lockForUpdate()
                ->findOrFail($id);

            // 2) Sahiplik kontrolü
            if ($ticket->rezervation->user_id !== $actorId) {
                // 404 → “var ama senin değil” bilgisini sızdırmamak için
                abort(Response::HTTP_NOT_FOUND, 'Ticket not found');
            }

            // 3) Durum kontrolü (kullanılmamış olmalı)
            if (!in_array($ticket->status, ['active', 'transferred'], true)) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Ticket cannot be transferred.');
            }

            // 4) Etkinlik zamanı kontrolü (başlamadı)
            $start = Carbon::parse($ticket->rezervation->event->start_date);
            if ($start->isPast()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Event already started.');
            }

            // 5) Fiyatı bul (rezervation_item varsa ondan, yoksa seat.price)
            $itemPrice = RezervationItem::where('rezervation_id', $ticket->rezervation_id)
                ->where('seat_id', $ticket->seat_id)
                ->value('price');
            $price = $itemPrice ?? optional($ticket->seat)->price ?? 0;

            // 6) Hedef kullanıcı için yeni CONFIRMED rezervasyon oluştur
            $newRez = Rezervation::create([
                'user_id'      => $toUserId,
                'event_id'     => $ticket->rezervation->event->id,
                'status'       => 'confirmed',
                'total_amount' => $price,
                'expires_at'   => null,
            ]);

            // 7) Yeni rezervasyon kalemi
            RezervationItem::create([
                'rezervation_id' => $newRez->id,
                'seat_id'        => $ticket->seat_id,
                'price'          => $price,
            ]);

            // 8) Bileti yeni rezervasyona taşı ve durumunu güncelle
            $ticket->update([
                'rezervation_id' => $newRez->id,
                'status'         => 'transferred', // istersen 'active' bırak + ayrı log tut
            ]);

            return $ticket->fresh(['rezervation', 'seat']);
        });

        return $this->successResponse($updated, 'Ticket transferred successfully');
    }
    public function cancel(TicketCancelRequest $request, int $id): JsonResponse
    {
        $actorId = $request->user()->id;

        $updated = DB::transaction(function () use ($id, $actorId) {

            // 1) Bileti kilitle
            $ticket = Ticket::with(['rezervation.event', 'seat'])
                ->lockForUpdate()
                ->findOrFail($id);

            // 2) Sahiplik
            if ($ticket->rezervation->user_id !== $actorId) {
                abort(Response::HTTP_NOT_FOUND, 'Ticket not found');
            }

            // 3) Uygun durum mu?
            if (!in_array($ticket->status, ['active', 'transferred'], true)) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Ticket cannot be cancelled.');
            }

            // 4) Zaman kuralı: etkinlik başlangıcına ≥ 24 saat olmalı
            $start = Carbon::parse($ticket->rezervation->event->start_date);
            if (now()->diffInHours($start, false) < 24) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cancellation window has passed (24h).');
            }

            // 5) Bileti iptal et
            $ticket->update(['status' => 'cancelled']);

            // 6) Koltuğu tekrar satılabilir yap
            Seat::where('id', $ticket->seat_id)->update([
                'status'         => Seat::STATUS_AVAILABLE,
                'reserved_by'    => null,
                'reserved_until' => null,
            ]);

            // (opsiyonel) Eski rezervasyon tutarını düşmek istersen burada decrement edebilirsin.

            return $ticket->fresh(['rezervation', 'seat']);
        });

        return $this->successResponse($updated, 'Ticket cancelled successfully');
    }

    /**
     * Download ticket as PDF (bonus)
     */
    public function download(Request $request, int $id)
    {
        $userId = $request->user()->id;

        $ticket = Ticket::with(['seat', 'rezervation.event'])
            ->where('id', $id)
            ->whereHas('rezervation', fn($qr) => $qr->where('user_id', $userId))
            ->first();

        if (!$ticket) {
            return $this->notFoundResponse('Ticket not found');
        }

        $html = view('tickets.pdf', [
            'ticket' => $ticket,
        ])->render();

        // If dompdf installed (barryvdh/laravel-dompdf)
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4');
            return $pdf->download('ticket-'.$ticket->id.'.pdf');
        }

        // Fallback: package yoksa bilgilendir
                    return $this->errorResponse(
                'PDF package not installed. Please install barryvdh/laravel-dompdf to enable downloads.',
                Response::HTTP_NOT_IMPLEMENTED,
                [
                    'install_command' => 'composer require barryvdh/laravel-dompdf && php artisan vendor:publish --provider="Barryvdh\\DomPDF\\ServiceProvider"'
                ]
            );
    }
}
