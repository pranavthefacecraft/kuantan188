import React from 'react';
import { Form } from 'react-bootstrap';
import './PaymentMethodSelector.css';

export type PaymentMethod = 'billplz' | 'cash_on_delivery';

interface PaymentMethodSelectorProps {
  selectedMethod: PaymentMethod;
  onMethodChange: (method: PaymentMethod) => void;
  totalAmount: number;
  currency?: string;
}

const PaymentMethodSelector: React.FC<PaymentMethodSelectorProps> = ({
  selectedMethod,
  onMethodChange,
  totalAmount,
  currency = '₹'
}) => {
  return (
    <div className="payment-method-selector">
      <h6 className="fw-bold mb-3">Payment Method</h6>
      
      {/* Billplz Online Payment Option */}
      <div className="payment-option mb-3">
        <div className={`p-4 border rounded position-relative ${selectedMethod === 'billplz' ? 'border-primary bg-light' : ''}`}>
          <Form.Check
            type="radio"
            id="payment-billplz"
            name="paymentMethod"
            value="billplz"
            checked={selectedMethod === 'billplz'}
            onChange={(e) => onMethodChange(e.target.value as PaymentMethod)}
            className="position-absolute"
            style={{ top: '20px', right: '20px' }}
          />
          
          <div className="d-flex align-items-start">
            <div className="payment-icon me-3">
              <div 
              className="d-flex align-items-center justify-content-center"
             
            >
             <img 
              src="/billplz.png" 
              alt="logo"
              style={{ width: '100%', height: '100%', objectFit: 'contain' }} 
            />
            </div>
            </div>
            
            <div className="flex-grow-1">
              <h6 className="mb-2 fw-bold" style={{ color: '#1A0007' }}>
  Online Payment via Billplz
</h6>
              <div className="text-muted mb-3">
                Pay securely online with your preferred method.
                <ul className="mt-2 mb-0">
                  <li>Credit/Debit Cards, Online Banking</li>
                  <li>Instant booking confirmation</li>
                  <li>Secure payment gateway</li>
                </ul>
              </div>
              
              {/* Billplz Accepted Methods */}
              <div className="accepted-methods d-flex flex-wrap gap-2 mt-3">
                <span className="badge bg-light text-dark border px-2 py-1">
                  💳 Credit Card
                </span>
                <span className="badge bg-light text-dark border px-2 py-1">
                  🏦 Online Banking
                </span>
                <span className="badge bg-light text-dark border px-2 py-1">
                  📱 E-Wallet
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Cash on Delivery Option */}
      <div className="payment-option mb-3">
        <div className={`p-4 border rounded position-relative ${selectedMethod === 'cash_on_delivery' ? 'border-primary bg-light' : ''}`}>
          <Form.Check
            type="radio"
            id="payment-cod"
            name="paymentMethod"
            value="cash_on_delivery"
            checked={selectedMethod === 'cash_on_delivery'}
            onChange={(e) => onMethodChange(e.target.value as PaymentMethod)}
            className="position-absolute"
            style={{ top: '20px', right: '20px' }}
          />
          
          <div className="d-flex align-items-start">
            <div className="payment-icon me-3">
              <div 
                className="d-flex align-items-center justify-content-center rounded-circle"
                style={{ 
                  width: '50px', 
                  height: '50px', 
                  backgroundColor: '#66001D',
                  color: '#FFE6ED'            
                }}
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                </svg>
              </div>
            </div>
            
            <div className="flex-grow-1">
              <h6 className="mb-2 fw-bold #1A0007">Cash on Delivery</h6>
              <div className="text-muted mb-3">
                Pay with cash when you arrive at the event.
                <ul className="mt-2 mb-0">
                  <li>No online payment required</li>
                  <li>Pay at the venue on event day</li>
                  <li>Booking confirmed instantly</li>
                </ul>
              </div>
              
              <div className="accepted-methods d-flex flex-wrap gap-2 mt-3">
                <span className="badge bg-light text-dark border px-2 py-1">
                  💵 Cash
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Payment Summary */}
      <div className="payment-summary mt-4 p-3 bg-light border rounded">
        <div className="d-flex justify-content-between align-items-center">
          <span className="fw-bold">Total Amount:</span>
          <span className="fs-5 fw-bold" style={{ color: '#66001D' }}>
            {currency}{totalAmount.toLocaleString()}
          </span>
        </div>
        
        <small className="text-muted d-block mt-2">
          {selectedMethod === 'billplz' 
            ? '🔒 Secure payment processed by Billplz' 
            : '📋 Your booking will be confirmed. Pay at the venue.'}
        </small>
      </div>
    </div>
  );
};

export default PaymentMethodSelector;