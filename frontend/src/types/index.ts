export interface User {
  id: number;
  name: string;
  email: string;
  role?: string;
}

export interface Country {
  id: number;
  name: string;
  code: string;
  currency_code: string;
  currency_symbol: string;
  price_multiplier: number;
  is_active: boolean;
}

export interface Event {
  id: number;
  title: string;
  description?: string;
  location?: string;
  event_date: string;
  booking_start_date?: string;
  booking_end_date?: string;
  image_url?: string;
  is_active: boolean;
}

export interface Ticket {
  id: number;
  event_id: number;
  country_id: number;
  ticket_type: 'adult' | 'child';
  base_price: number;
  final_price: number;
  total_quantity: number;
  available_quantity: number;
  description?: string;
  is_active: boolean;
  event?: Event;
  country?: Country;
}

export interface Booking {
  id: number;
  booking_reference: string;
  event_id: number;
  country_id: number;
  customer_name: string;
  customer_email: string;
  customer_phone?: string;
  adult_tickets: number;
  child_tickets: number;
  adult_price: number;
  child_price: number;
  total_amount: number;
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  payment_method?: string;
  payment_reference?: string;
  payment_date?: string;
  status: 'pending' | 'confirmed' | 'cancelled';
  event?: Event;
  country?: Country;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface BookingFormData {
  event_id: number;
  country_id: number;
  customer_name: string;
  customer_email: string;
  customer_phone?: string;
  adult_tickets: number;
  child_tickets: number;
}