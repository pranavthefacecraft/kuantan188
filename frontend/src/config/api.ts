export const API_BASE_URL = 'http://127.0.0.1:8000/api';

export const API_ENDPOINTS = {
  // Authentication
  LOGIN: '/auth/login',
  REGISTER: '/auth/register',
  LOGOUT: '/auth/logout',
  USER: '/auth/user',
  
  // Countries
  COUNTRIES: '/countries',
  
  // Events
  EVENTS: '/events',
  
  // Tickets
  TICKETS: '/tickets',
  
  // Bookings
  BOOKINGS: '/bookings',
};

export default {
  API_BASE_URL,
  API_ENDPOINTS,
};