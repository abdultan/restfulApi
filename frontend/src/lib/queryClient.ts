import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes
      retry: (failureCount, error: any) => {
        // Don't retry on 4xx errors except 408, 429
        if (error?.response?.status >= 400 && error?.response?.status < 500) {
          if (![408, 429].includes(error.response.status)) {
            return false;
          }
        }
        return failureCount < 3;
      },
    },
    mutations: {
      retry: false,
    },
  },
});

export const queryKeys = {
  events: (filters?: any) => ['events', filters],
  event: (id: number) => ['event', id],
  seats: (eventId: number) => ['seats', eventId],
  reservations: () => ['reservations'],
  reservation: (id: number) => ['reservation', id],
  tickets: (filters?: any) => ['tickets', filters],
  ticket: (id: number) => ['ticket', id],
  venues: () => ['venues'],
  venue: (id: number) => ['venue', id],
  user: () => ['user'],
} as const;