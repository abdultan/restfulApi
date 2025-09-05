export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  role: 'user' | 'admin';
  created_at: string;
  updated_at: string;
}

export interface Event {
  id: number;
  name: string;
  description: string;
  venue_id: number;
  venue?: Venue;
  start_date: string;
  end_date: string;
  status: 'active' | 'inactive' | 'sold_out';
  created_at: string;
  updated_at: string;
}

export interface Venue {
  id: number;
  name: string;
  address: string;
  capacity: number;
  created_at: string;
  updated_at: string;
}

export interface Seat {
  id: number;
  event_id: number;
  row: string;
  number: string;
  price: number;
  status: 'available' | 'sold' | 'reserved';
  label: string;
}

export interface Reservation {
  id: number;
  user_id: number;
  event_id: number;
  event?: Event;
  seats: ReservationSeat[];
  total_price: number;
  status: 'pending' | 'confirmed' | 'cancelled';
  expires_at: string;
  created_at: string;
  updated_at: string;
}

export interface ReservationSeat {
  id: number;
  seat_id: number;
  seat?: Seat;
  price: number;
}

export interface Ticket {
  id: number;
  user_id: number;
  event_id: number;
  event?: Event;
  seat_id: number;
  seat?: Seat;
  price: number;
  status: 'active' | 'cancelled' | 'transferred';
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface ApiResponse<T = any> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T = any> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface EventFilters {
  search?: string;
  venue_id?: number;
  status?: string;
  start_date?: string;
  end_date?: string;
  sort?: 'start_date' | '-start_date';
  page?: number;
  per_page?: number;
}

export interface TicketFilters {
  status?: 'active' | 'cancelled' | 'transferred';
  event_id?: number;
  per_page?: number;
  page?: number;
}