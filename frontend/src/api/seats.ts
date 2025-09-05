import api from "../lib/axios";
import { Seat } from "../types/api";

export const seatsApi = {
    getEventSeats: async (eventId: number): Promise<Seat[]> => {
        const response = await api.get(`/events/${eventId}/seats`);
        const payload = response.data?.data;
        return payload?.data ?? payload ?? [];
    },

    blockSeats: async (eventId: number, seatIds: number[]): Promise<void> => {
        await api.post("/seats/block", {
            event_id: eventId,
            seat_ids: seatIds,
        });
    },

    releaseSeats: async (seatIds: number[]): Promise<void> => {
        await api.delete("/seats/release", {
            data: { seat_ids: seatIds },
        });
    },
};
