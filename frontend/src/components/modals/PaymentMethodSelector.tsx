import React from 'react';
import { Form } from 'react-bootstrap';
import './PaymentMethodSelector.css';

export type PaymentMethod = 'cash_on_delivery' | 'billplz';

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
      
      {/* Cash on Delivery Option */}
      <div className="payment-option mb-3">
        <div className="p-4 border rounded position-relative">
          <Form.Check
            type="radio"
            id="payment-cash"
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
                className="d-flex align-items-center justify-content-center rounded-circle bg-light"
                style={{ width: '50px', height: '50px' }}
              >
                💰
              </div>
            </div>
            
            <div className="flex-grow-1">
              <h6 className="mb-2 fw-bold text-primary">Cash on Delivery</h6>
              <div className="text-muted mb-3">
                Pay with cash when your booking is confirmed.
                <ul className="mt-2 mb-0">
                  <li>No advance payment required</li>
                  <li>Pay upon booking confirmation</li>
                  <li>Secure and reliable</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Billplz Online Payment Option */}
      <div className="payment-option mb-3">
        <div className="p-4 border rounded position-relative">
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
                className="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                style={{ width: '50px', height: '50px' }}
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
              </div>
            </div>
            
            <div className="flex-grow-1">
              <h6 className="mb-2 fw-bold text-primary">Online Payment via Billplz</h6>
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

      {/* Payment Summary */}
      <div className="payment-summary mt-4 p-3 bg-light border rounded">
        <div className="d-flex justify-content-between align-items-center">
          <span className="fw-bold">Total Amount:</span>
          <span className="fs-5 fw-bold text-primary">
            {currency}{totalAmount.toLocaleString()}
          </span>
        </div>
        
        {selectedMethod === 'cash_on_delivery' && (
          <small className="text-muted d-block mt-2">
            💡 Amount will be collected upon booking confirmation
          </small>
        )}
        
        {selectedMethod === 'billplz' && (
          <small className="text-muted d-block mt-2">
            🔒 Secure payment processed by Billplz
          </small>
        )}
      </div>
    </div>
  );
};

export default PaymentMethodSelector;