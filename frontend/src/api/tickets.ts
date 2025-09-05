import api from "../lib/axios";
import { Ticket, TicketFilters, PaginatedResponse } from "../types/api";

export const ticketsApi = {
    getTickets: async (
        filters: TicketFilters = {}
    ): Promise<PaginatedResponse<Ticket>> => {
        const params = new URLSearchParams();

        Object.entries(filters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== "") {
                params.append(key, value.toString());
            }
        });

        const response = await api.get(`/tickets?${params.toString()}`);
        const payload = response.data?.data;
        return {
            data: payload?.data ?? [],
            current_page: payload?.meta?.current_page ?? 1,
            last_page: payload?.meta?.last_page ?? 1,
            per_page: payload?.meta?.per_page ?? (filters.per_page || 10),
            total: payload?.meta?.total ?? payload?.data?.length ?? 0,
        };
    },

    getTicket: async (id: number): Promise<Ticket> => {
        const response = await api.get(`/tickets/${id}`);
        return response.data.data;
    },

    cancelTicket: async (id: number, reason?: string): Promise<Ticket> => {
        const response = await api.post(`/tickets/${id}/cancel`, { reason });
        return response.data.data;
    },

    transferTicket: async (id: number, email: string): Promise<Ticket> => {
        const response = await api.post(`/tickets/${id}/transfer`, { email });
        return response.data.data;
    },

    downloadTicket: async (id: number): Promise<Blob> => {
        const response = await api.get(`/tickets/${id}/download`, {
            responseType: "blob",
        });
        return response.data;
    },
};
