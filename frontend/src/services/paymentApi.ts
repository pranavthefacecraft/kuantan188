import apiClient from './api';

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
      
      const response = await apiClient.post<BookingResponse>('/public/bookings', {
        ...bookingData,
        booking_status: bookingData.payment_method === 'billplz' ? 'pending' : 'confirmed'
      });
      
      console.log('[PAYMENT API] Booking created:', response.data);
      return response.data;
    } catch (error) {
      console.error('[PAYMENT API] Error creating booking:', error);
      throw new Error('Failed to create booking. Please try again.');
    }
  }

  /**
   * Create Billplz payment for a booking
   */
  async createPayment(paymentData: PaymentData): Promise<PaymentResponse> {
    try {
      console.log('[PAYMENT API] Creating Billplz payment:', paymentData);
      
      const response = await apiClient.post<PaymentResponse>('/public/payment/billplz/create', paymentData);
      
      console.log('[PAYMENT API] Payment created:', response.data);
      return response.data;
    } catch (error) {
      console.error('[PAYMENT API] Error creating payment:', error);
      throw new Error('Failed to create payment. Please try again.');
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
      const bookingResponse = await this.createBooking({
        ...bookingData,
        booking_status: 'pending'
      });

      if (!bookingResponse.success) {
        throw new Error(bookingResponse.message || 'Booking creation failed');
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
        throw new Error(paymentResponse.message || 'Payment creation failed');
      }

      return {
        bookingResponse,
        paymentUrl: paymentResponse.data.payment_url
      };
    } catch (error) {
      console.error('[PAYMENT API] Payment flow error:', error);
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