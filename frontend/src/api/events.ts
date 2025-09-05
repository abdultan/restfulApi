import api from "../lib/axios";
import { Event, EventFilters, PaginatedResponse } from "../types/api";

export const eventsApi = {
    getEvents: async (
        filters: EventFilters = {}
    ): Promise<PaginatedResponse<Event>> => {
        const params = new URLSearchParams();

        // Map frontend filter keys to backend query params
        if (filters.search) params.append("q", filters.search);
        if (filters.venue_id)
            params.append("venue_id", String(filters.venue_id));
        if (filters.status) params.append("status", String(filters.status));
        if (filters.start_date)
            params.append("from", String(filters.start_date));
        if (filters.end_date) params.append("to", String(filters.end_date));
        if (filters.page) params.append("page", String(filters.page));
        params.append("per_page", String(filters.per_page || 12));

        const response = await api.get(`/events?${params.toString()}`);
        const payload = response.data?.data;
        return {
            data: payload?.data ?? [],
            current_page: payload?.meta?.current_page ?? 1,
            last_page: payload?.meta?.last_page ?? 1,
            per_page: payload?.meta?.per_page ?? (filters.per_page || 10),
            total: payload?.meta?.total ?? payload?.data?.length ?? 0,
        };
    },

    getEvent: async (id: number): Promise<Event> => {
        const response = await api.get(`/events/${id}`);
        return response.data.data;
    },

    createEvent: async (eventData: Partial<Event>): Promise<Event> => {
        const response = await api.post("/events", eventData);
        return response.data.data;
    },

    updateEvent: async (
        id: number,
        eventData: Partial<Event>
    ): Promise<Event> => {
        const response = await api.put(`/events/${id}`, eventData);
        return response.data.data;
    },

    deleteEvent: async (id: number): Promise<void> => {
        await api.delete(`/events/${id}`);
    },
};
