import api from "../lib/axios";
import { Reservation } from "../types/api";

export const reservationsApi = {
    getReservations: async (): Promise<Reservation[]> => {
        const response = await api.get("/rezervations");
        const payload = response.data?.data;
        const raw = payload?.data ?? payload ?? [];
        // Adapt backend RezervationResource -> frontend Reservation type
        return raw.map((r: any) => ({
            id: r.id,
            user_id: r.user_id ?? undefined,
            event_id: r.event?.id ?? r.event_id,
            event: r.event,
            seats: (r.items || []).map((it: any) => ({
                id: it.id,
                seat_id: it.seat?.id ?? it.seat_id,
                seat: it.seat
                    ? {
                          id: it.seat.id,
                          event_id: r.event?.id ?? 0,
                          row: String(it.seat.row ?? ""),
                          number: String(it.seat.number ?? ""),
                          price: it.seat.price,
                          status: it.seat.status ?? "available",
                          label:
                              it.seat.label ??
                              `${it.seat.section}-${it.seat.row}${it.seat.number}`,
                      }
                    : undefined,
                price: it.price,
            })),
            total_price: r.total_amount ?? r.total_price,
            status: r.status,
            expires_at: r.expires_at,
            created_at: r.created_at,
            updated_at: r.updated_at,
        }));
    },

    getReservation: async (id: number): Promise<Reservation> => {
        const response = await api.get(`/rezervations/${id}`);
        const r = response.data?.data;
        return {
            id: r.id,
            user_id: r.user_id ?? undefined,
            event_id: r.event?.id ?? r.event_id,
            event: r.event,
            seats: (r.items || []).map((it: any) => ({
                id: it.id,
                seat_id: it.seat?.id ?? it.seat_id,
                seat: it.seat
                    ? {
                          id: it.seat.id,
                          event_id: r.event?.id ?? 0,
                          row: String(it.seat.row ?? ""),
                          number: String(it.seat.number ?? ""),
                          price: it.seat.price,
                          status: it.seat.status ?? "available",
                          label:
                              it.seat.label ??
                              `${it.seat.section}-${it.seat.row}${it.seat.number}`,
                      }
                    : undefined,
                price: it.price,
            })),
            total_price: r.total_amount ?? r.total_price,
            status: r.status,
            expires_at: r.expires_at,
            created_at: r.created_at,
            updated_at: r.updated_at,
        } as Reservation;
    },

    createReservation: async (
        eventId: number,
        seatIds: number[]
    ): Promise<Reservation> => {
        const response = await api.post("/rezervations", {
            event_id: eventId,
            seat_ids: seatIds,
        });
        return response.data.data;
    },

    confirmReservation: async (id: number): Promise<Reservation> => {
        const response = await api.post(`/rezervations/${id}/confirm`);
        const r = response.data?.data;
        return {
            id: r.id,
            user_id: r.user_id ?? undefined,
            event_id: r.event?.id ?? r.event_id,
            event: r.event,
            seats: (r.items || []).map((it: any) => ({
                id: it.id,
                seat_id: it.seat?.id ?? it.seat_id,
                seat: it.seat
                    ? {
                          id: it.seat.id,
                          event_id: r.event?.id ?? 0,
                          row: String(it.seat.row ?? ""),
                          number: String(it.seat.number ?? ""),
                          price: it.seat.price,
                          status: it.seat.status ?? "available",
                          label:
                              it.seat.label ??
                              `${it.seat.section}-${it.seat.row}${it.seat.number}`,
                      }
                    : undefined,
                price: it.price,
            })),
            total_price: r.total_amount ?? r.total_price,
            status: r.status,
            expires_at: r.expires_at,
            created_at: r.created_at,
            updated_at: r.updated_at,
        } as Reservation;
    },

    deleteReservation: async (id: number): Promise<void> => {
        await api.delete(`/rezervations/${id}`);
    },
};
