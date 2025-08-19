<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bilet #{{ $ticket->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; margin: 0; padding: 24px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .row { display: flex; gap: 16px; }
        .col { flex: 1; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 16px; margin: 16px 0 8px; }
        .muted { color: #666; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; background: #f2f2f2; font-size: 12px; }
        .divider { height: 1px; background: #eee; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 6px 0; border-bottom: 1px solid #f0f0f0; }
        .right { text-align: right; }
    </style>
    </head>
<body>
    <div class="card">
        <div class="row">
            <div class="col">
                <h1>{{ optional($ticket->rezervation->event)->name ?? 'Etkinlik' }}</h1>
                <div class="muted">
                    Tarih: {{ optional($ticket->rezervation->event)->start_date }}
                </div>
                <div class="muted">
                    Bilet Kodu: <strong>{{ $ticket->ticket_code }}</strong>
                    <span class="badge">{{ strtoupper($ticket->status) }}</span>
                </div>
            </div>
            <div class="col right">
                <div>#{{ $ticket->id }}</div>
                <div class="muted">{{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="divider"></div>

        <h2>Koltuk Bilgileri</h2>
        <table>
            <tr>
                <th>Salon (Venue)</th>
                <td>#{{ optional($ticket->seat)->venue_id }}</td>
            </tr>
            <tr>
                <th>Bölüm</th>
                <td>{{ optional($ticket->seat)->section }}</td>
            </tr>
            <tr>
                <th>Sıra</th>
                <td>{{ optional($ticket->seat)->row }}</td>
            </tr>
            <tr>
                <th>Koltuk</th>
                <td>{{ optional($ticket->seat)->number }}</td>
            </tr>
            <tr>
                <th>Fiyat</th>
                <td>
                    {{ number_format(
                        \App\Models\RezervationItem::where('rezervation_id', $ticket->rezervation_id)
                            ->where('seat_id', $ticket->seat_id)
                            ->value('price') ?? (optional($ticket->seat)->price ?? 0),
                        2, ',', '.'
                    ) }} ₺
                </td>
            </tr>
        </table>

        <div class="divider"></div>
        <div class="muted">
            Bu bilet kişiye özeldir. Etkinlik kurallarına ve giriş koşullarına tabidir. Girişte kimlik istenebilir.
        </div>
    </div>
</body>
</html>


