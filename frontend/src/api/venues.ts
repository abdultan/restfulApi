import api from "../lib/axios";
import { Venue } from "../types/api";

export const venuesApi = {
    getVenues: async (): Promise<{ data: Venue[] }> => {
        const response = await api.get("/venues");
        return { data: response.data?.data ?? [] };
    },

    getVenue: async (id: number): Promise<Venue> => {
        const response = await api.get(`/venues/${id}`);
        return response.data.data;
    },

    createVenue: async (venueData: Partial<Venue>): Promise<Venue> => {
        const response = await api.post("/venues", venueData);
        return response.data.data;
    },

    updateVenue: async (
        id: number,
        venueData: Partial<Venue>
    ): Promise<Venue> => {
        const response = await api.put(`/venues/${id}`, venueData);
        return response.data.data;
    },

    deleteVenue: async (id: number): Promise<void> => {
        await api.delete(`/venues/${id}`);
    },
};
