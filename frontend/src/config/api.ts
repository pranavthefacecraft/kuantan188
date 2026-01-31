// API Configuration for different environments
export const API_BASE_URL = process.env.REACT_APP_API_URL || 
  (process.env.NODE_ENV === 'production' 
    ? 'https://admin.tfcmockup.com/api'   // Production: Laravel admin subdomain
    : 'http://127.0.0.1:8000/api');        // Development: Local Laravel

export const API_ENDPOINTS = {
  // Authentication
  LOGIN: '/auth/login',
  REGISTER: '/auth/register',
  LOGOUT: '/auth/logout',
  USER: '/auth/user',
  
  // Countries
  COUNTRIES: '/countries',
  
  // Events - using public routes for frontend
  EVENTS: '/public/events',
  
  // Tickets - using public routes for frontend
  TICKETS: '/public/tickets',
  
  // Bookings - using public routes for frontend
  BOOKINGS: '/public/bookings',
};

const apiConfig = {
  API_BASE_URL,
  API_ENDPOINTS,
};

export default apiConfig;