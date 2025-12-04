import axios from 'axios';
import { API_BASE_URL } from '../config/api';

// Event interfaces
export interface Event {
  id: number;
  title: string;
  description: string;
  location: string;
  event_date: string;
  event_date_formatted: string;
  event_time_formatted: string;
  image_url: string;
  price: string | number;
  child_price?: number;
  price_display: string;
  category: string;
  is_booking_open: boolean;
  slug: string;
  ticket_pricing?: {
    base_price: number;
    adult_price_range?: { min: number; max: number } | null;
    child_price_range?: { min: number; max: number } | null;
    countries_available: Array<{
      name: string;
      code: string;
      currency_symbol: string;
      adult_price: number;
      child_price: number;
    }>;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  total?: number;
  message?: string;
}

// Create axios instance
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add request interceptor to include auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Public Events API functions
export const eventsApi = {
  // Get all events with optional search
  async getEvents(search?: string): Promise<ApiResponse<Event[]>> {
    const params = search ? { search } : {};
    const response = await apiClient.get('/public/events', { params });
    return response.data;
  },

  // Get featured events for homepage
  async getFeaturedEvents(): Promise<ApiResponse<Event[]>> {
    const response = await apiClient.get('/public/events/featured');
    return response.data;
  },

  // Get single event details
  async getEvent(id: number): Promise<ApiResponse<Event>> {
    const response = await apiClient.get(`/public/events/${id}`);
    return response.data;
  },

  // Get single event by ID (alias for consistency)
  async getEventById(id: number): Promise<ApiResponse<Event>> {
    const response = await apiClient.get(`/public/events/${id}`);
    return response.data;
  },

  // Get user tickets/bookings
  async getTickets(): Promise<ApiResponse<any[]>> {
    const response = await apiClient.get('/public/tickets');
    return response.data;
  },

  // Get Book Now events with category filtering
  async getBookNowEvents(category?: string): Promise<ApiResponse<Event[]>> {
    const params = category && category !== 'all' ? { category } : {};
    const response = await apiClient.get('/public/events/book-now', { params });
    return response.data;
  }
};

export default apiClient;