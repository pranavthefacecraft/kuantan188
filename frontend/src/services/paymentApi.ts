import apiClient from './api';
import frontendLogger from '../utils/logger';

export interface PaymentData {
  booking_id: number;
  amount: number;
  customer_name: string;
  customer_email: string;
  description: string;
}

export interface PaymentResponse {
  success: boolean;
  data: {
    payment_url: string;
    bill_id: string;
    booking_reference: string;
  };
  message?: string;
}

export interface BookingData {
  // Event booking specific fields
  event_id?: number;
  event_title: string;
  
  // Ticket booking specific fields
  ticket_id?: number;
  
  // Customer information
  customer_name: string;
  email: string;
  mobile_phone: string;
  country: string;
  postal_code?: string;
  
  // Booking details
  quantity: number;
  adult_tickets?: number;
  child_tickets?: number;
  adult_price?: number;
  child_price?: number;
  event_date: string;
  selected_time?: string;
  is_all_day_pass?: boolean;
  
  // Payment information
  total_amount: number;
  payment_method: 'cash_on_delivery' | 'billplz';
  booking_status: 'pending' | 'confirmed';
  
  // Optional fields
  receive_updates?: boolean;
}

export interface BookingResponse {
  success: boolean;
  booking: {
    id: number;
    booking_reference: string;
    payment_method: string;
    booking_status: string;
    total_amount: number;
  };
  message?: string;
}

export interface PaymentStatusResponse {
  success: boolean;
  data: {
    booking_id: number;
    payment_status: 'pending' | 'paid' | 'failed';
    booking_status: 'pending' | 'confirmed' | 'cancelled';
    payment_amount: number;
    bill_id?: string;
    paid_at?: string;
  };
  message?: string;
}

class PaymentApiService {
  /**
   * Create a new booking with pending status for online payment
   */
  async createBooking(bookingData: BookingData): Promise<BookingResponse> {
    try {
      console.log('[PAYMENT API] Creating booking with data:', bookingData);
      frontendLogger.info('Creating booking', { bookingData });
      
      const response = await apiClient.post<BookingResponse>('/public/bookings', {
        ...bookingData,
        booking_status: bookingData.payment_method === 'billplz' ? 'pending' : 'confirmed'
      });
      
      console.log('[PAYMENT API] Booking created:', response.data);
      frontendLogger.info('Booking created successfully', { response: response.data });
      return response.data;
    } catch (error: any) {
      console.error('[PAYMENT API] Error creating booking:', error);
      
      const errorMessage = error.response?.data?.message || error.message || 'Failed to create booking';
      const errorDetails = {
        status: error.response?.status,
        data: error.response?.data,
        config: {
          url: error.config?.url,
          method: error.config?.method,
          data: error.config?.data
        }
      };
      
      frontendLogger.logApiError(
        '/public/bookings',
        'POST',
        error.response?.status || 0,
        errorMessage,
        bookingData,
        error.response?.data
      );
      
      frontendLogger.logPaymentError('Booking Creation', errorMessage, errorDetails);
      
      throw new Error(`Booking failed: ${errorMessage}`);
    }
  }

  /**
   * Create Billplz payment for a booking
   */
  async createPayment(paymentData: PaymentData): Promise<PaymentResponse> {
    try {
      console.log('[PAYMENT API] Creating Billplz payment:', paymentData);
      frontendLogger.info('Creating Billplz payment', { paymentData });
      
      const response = await apiClient.post<PaymentResponse>('/public/payment/billplz/create', paymentData);
      
      console.log('[PAYMENT API] Payment created:', response.data);
      frontendLogger.info('Billplz payment created successfully', { response: response.data });
      return response.data;
    } catch (error: any) {
      console.error('[PAYMENT API] Error creating payment:', error);
      
      const errorMessage = error.response?.data?.message || error.message || 'Failed to create payment';
      const errorDetails = {
        status: error.response?.status,
        data: error.response?.data,
        config: {
          url: error.config?.url,
          method: error.config?.method,
          data: error.config?.data
        }
      };
      
      frontendLogger.logApiError(
        '/public/payment/billplz/create',
        'POST',
        error.response?.status || 0,
        errorMessage,
        paymentData,
        error.response?.data
      );
      
      frontendLogger.logPaymentError('Payment Creation', errorMessage, errorDetails);
      
      throw new Error(`Payment creation failed: ${errorMessage}`);
    }
  }

  /**
   * Check payment status for a booking
   */
  async checkPaymentStatus(bookingId: number): Promise<PaymentStatusResponse> {
    try {
      console.log('[PAYMENT API] Checking payment status for booking:', bookingId);
      
      const response = await apiClient.get<PaymentStatusResponse>(`/public/payment/status/${bookingId}`);
      
      console.log('[PAYMENT API] Payment status:', response.data);
      return response.data;
    } catch (error) {
      console.error('[PAYMENT API] Error checking payment status:', error);
      throw new Error('Failed to check payment status.');
    }
  }

  /**
   * Handle payment completion (called from callback page)
   */
  async handlePaymentCallback(billplzId: string): Promise<PaymentStatusResponse> {
    try {
      console.log('[PAYMENT API] Processing payment callback for bill:', billplzId);
      
      const response = await apiClient.post<PaymentStatusResponse>('/public/payment/billplz/callback', {
        billplz_id: billplzId
      });
      
      console.log('[PAYMENT API] Callback processed:', response.data);
      return response.data;
    } catch (error) {
      console.error('[PAYMENT API] Error processing callback:', error);
      throw new Error('Failed to process payment callback.');
    }
  }

  /**
   * Complete payment flow: Create booking → Create payment → Redirect
   */
  async processPayment(bookingData: BookingData): Promise<{ bookingResponse: BookingResponse; paymentUrl: string }> {
    try {
      // Step 1: Create booking with pending status
      console.log('[PAYMENT API] Starting payment flow...');
      frontendLogger.logPaymentStart(bookingData.payment_method, bookingData.total_amount, { bookingData });
      
      const bookingResponse = await this.createBooking({
        ...bookingData,
        booking_status: 'pending'
      });

      if (!bookingResponse.success) {
        const error = bookingResponse.message || 'Booking creation failed';
        frontendLogger.logPaymentError('Payment Flow - Booking', error, { bookingResponse });
        throw new Error(error);
      }

      // Step 2: Create Billplz payment
      const paymentData: PaymentData = {
        booking_id: bookingResponse.booking.id,
        amount: bookingData.total_amount,
        customer_name: bookingData.customer_name,
        customer_email: bookingData.email,
        description: `Event Booking Payment - ${bookingData.event_title}`
      };

      const paymentResponse = await this.createPayment(paymentData);

      if (!paymentResponse.success) {
        const error = paymentResponse.message || 'Payment creation failed';
        frontendLogger.logPaymentError('Payment Flow - Payment Creation', error, { paymentResponse });
        throw new Error(error);
      }

      frontendLogger.info('Payment flow completed successfully', {
        bookingId: bookingResponse.booking.id,
        paymentUrl: paymentResponse.data.payment_url
      });

      return {
        bookingResponse,
        paymentUrl: paymentResponse.data.payment_url
      };
    } catch (error) {
      console.error('[PAYMENT API] Payment flow error:', error);
      frontendLogger.logPaymentError('Payment Flow', error instanceof Error ? error.message : 'Unknown error', { bookingData });
      throw error;
    }
  }

  /**
   * Process cash on delivery booking
   */
  async processCashOnDelivery(bookingData: BookingData): Promise<BookingResponse> {
    try {
      console.log('[PAYMENT API] Processing cash on delivery booking...');
      
      return await this.createBooking({
        ...bookingData,
        booking_status: 'confirmed'
      });
    } catch (error) {
      console.error('[PAYMENT API] Cash on delivery error:', error);
      throw error;
    }
  }
}

export const paymentApi = new PaymentApiService();
export default paymentApi;