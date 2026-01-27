import React from 'react';
import { Modal, Spinner } from 'react-bootstrap';

interface PaymentLoadingProps {
  show: boolean;
  title?: string;
  message?: string;
}

const PaymentLoading: React.FC<PaymentLoadingProps> = ({ 
  show, 
  title = "Processing Payment",
  message = "Please wait while we process your payment..."
}) => {
  return (
    <Modal 
      show={show} 
      backdrop="static" 
      keyboard={false} 
      centered
      className="payment-loading-modal"
    >
      <Modal.Body className="text-center p-5">
        <div className="mb-4">
          <Spinner 
            animation="border" 
            variant="primary" 
            style={{ width: '3rem', height: '3rem' }}
          />
        </div>
        
        <h5 className="mb-3 text-primary">{title}</h5>
        <p className="text-muted mb-4">{message}</p>
        
        <div className="payment-steps">
          <div className="small text-muted">
            <div className="d-flex justify-content-center align-items-center mb-2">
              <i className="fas fa-check-circle text-success me-2"></i>
              <span>Booking details verified</span>
            </div>
            <div className="d-flex justify-content-center align-items-center mb-2">
              <Spinner size="sm" animation="border" className="me-2" />
              <span>Creating secure payment link...</span>
            </div>
            <div className="d-flex justify-content-center align-items-center">
              <i className="fas fa-clock text-muted me-2"></i>
              <span>Redirecting to payment gateway</span>
            </div>
          </div>
        </div>
        
        <div className="alert alert-info border-0 mt-4" style={{ backgroundColor: '#e7f3ff' }}>
          <div className="d-flex align-items-center justify-content-center">
            <i className="fas fa-info-circle text-primary me-2"></i>
            <small className="text-muted">
              <strong>Secure Payment:</strong> You will be redirected to Billplz for secure payment processing
            </small>
          </div>
        </div>
      </Modal.Body>
    </Modal>
  );
};

export default PaymentLoading;