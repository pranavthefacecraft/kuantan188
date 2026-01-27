import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { Container, Row, Col, Card, Button, Spinner, Alert } from 'react-bootstrap';
import paymentApi from '../services/paymentApi';

interface PaymentResult {
  success: boolean;
  bookingId: number;
  paymentStatus: 'pending' | 'paid' | 'failed';
  bookingStatus: 'pending' | 'confirmed' | 'cancelled';
  amount: number;
  bookingReference?: string;
}

const PaymentCallback: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [paymentResult, setPaymentResult] = useState<PaymentResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const processCallback = async () => {
      try {
        setLoading(true);
        setError(null);

        // Get parameters from URL (Billplz callback parameters)
        const billplzId = searchParams.get('billplz_id');
        const billplzPaid = searchParams.get('billplz_paid');
        const bookingId = searchParams.get('booking_id');

        console.log('[PAYMENT CALLBACK] Processing callback with params:', {
          billplzId,
          billplzPaid,
          bookingId
        });

        if (!billplzId) {
          throw new Error('Missing payment information. Please contact support.');
        }

        // Process the callback
        const result = await paymentApi.handlePaymentCallback(billplzId);
        
        if (result.success) {
          setPaymentResult({
            success: true,
            bookingId: result.data.booking_id,
            paymentStatus: result.data.payment_status,
            bookingStatus: result.data.booking_status,
            amount: result.data.payment_amount,
            bookingReference: `KB${result.data.booking_id}` // Generate reference if not provided
          });
        } else {
          throw new Error(result.message || 'Payment processing failed');
        }
      } catch (err) {
        console.error('[PAYMENT CALLBACK] Error processing callback:', err);
        setError(err instanceof Error ? err.message : 'An unexpected error occurred');
      } finally {
        setLoading(false);
      }
    };

    processCallback();
  }, [searchParams]);

  const handleContinue = () => {
    // Redirect to home page or events page
    navigate('/');
  };

  const handleTryAgain = () => {
    // Redirect back to events page for retry
    navigate('/events');
  };

  if (loading) {
    return (
      <Container className="d-flex justify-content-center align-items-center" style={{ minHeight: '60vh' }}>
        <div className="text-center">
          <Spinner animation="border" variant="primary" className="mb-3" style={{ width: '3rem', height: '3rem' }} />
          <h5 className="text-primary mb-2">Processing Payment</h5>
          <p className="text-muted">Please wait while we verify your payment...</p>
        </div>
      </Container>
    );
  }

  if (error) {
    return (
      <Container className="py-5">
        <Row className="justify-content-center">
          <Col md={6}>
            <Card className="text-center border-danger">
              <Card.Body className="py-5">
                <div className="mb-4">
                  <i className="fas fa-exclamation-triangle text-danger" style={{ fontSize: '4rem' }}></i>
                </div>
                <h4 className="text-danger mb-3">Payment Processing Error</h4>
                <Alert variant="danger" className="mb-4">
                  {error}
                </Alert>
                <p className="text-muted mb-4">
                  There was an issue processing your payment. Please try again or contact our support team.
                </p>
                <div className="d-grid gap-2">
                  <Button variant="danger" onClick={handleTryAgain}>
                    <i className="fas fa-redo me-2"></i>
                    Try Again
                  </Button>
                  <Button variant="outline-secondary" onClick={handleContinue}>
                    <i className="fas fa-home me-2"></i>
                    Back to Home
                  </Button>
                </div>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>
    );
  }

  if (!paymentResult) {
    return (
      <Container className="py-5">
        <Row className="justify-content-center">
          <Col md={6}>
            <Card className="text-center">
              <Card.Body className="py-5">
                <h4 className="mb-3">Payment Information Not Available</h4>
                <p className="text-muted">Unable to retrieve payment information.</p>
                <Button variant="primary" onClick={handleContinue}>
                  <i className="fas fa-home me-2"></i>
                  Return to Home
                </Button>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>
    );
  }

  const isPaymentSuccessful = paymentResult.paymentStatus === 'paid';
  const isBookingConfirmed = paymentResult.bookingStatus === 'confirmed';

  return (
    <Container className="py-5">
      <Row className="justify-content-center">
        <Col md={8}>
          <Card className={`text-center ${isPaymentSuccessful ? 'border-success' : 'border-warning'}`}>
            <Card.Body className="py-5">
              <div className="mb-4">
                {isPaymentSuccessful ? (
                  <i className="fas fa-check-circle text-success" style={{ fontSize: '4rem' }}></i>
                ) : (
                  <i className="fas fa-clock text-warning" style={{ fontSize: '4rem' }}></i>
                )}
              </div>

              {isPaymentSuccessful ? (
                <>
                  <h4 className="text-success mb-3">Payment Successful!</h4>
                  <p className="text-muted mb-4">
                    Your payment has been processed successfully and your booking is confirmed.
                  </p>
                </>
              ) : (
                <>
                  <h4 className="text-warning mb-3">Payment Processing</h4>
                  <p className="text-muted mb-4">
                    Your payment is being processed. Please wait for confirmation or check your email.
                  </p>
                </>
              )}

              {/* Payment Details */}
              <div className="payment-details mb-4">
                <Row>
                  <Col md={6}>
                    <div className="detail-item mb-3">
                      <div className="text-muted small">Booking Reference</div>
                      <div className="fw-bold">{paymentResult.bookingReference}</div>
                    </div>
                  </Col>
                  <Col md={6}>
                    <div className="detail-item mb-3">
                      <div className="text-muted small">Amount Paid</div>
                      <div className="fw-bold">RM{paymentResult.amount.toFixed(2)}</div>
                    </div>
                  </Col>
                  <Col md={6}>
                    <div className="detail-item mb-3">
                      <div className="text-muted small">Payment Status</div>
                      <div className={`fw-bold ${isPaymentSuccessful ? 'text-success' : 'text-warning'}`}>
                        {paymentResult.paymentStatus.charAt(0).toUpperCase() + paymentResult.paymentStatus.slice(1)}
                      </div>
                    </div>
                  </Col>
                  <Col md={6}>
                    <div className="detail-item mb-3">
                      <div className="text-muted small">Booking Status</div>
                      <div className={`fw-bold ${isBookingConfirmed ? 'text-success' : 'text-warning'}`}>
                        {paymentResult.bookingStatus.charAt(0).toUpperCase() + paymentResult.bookingStatus.slice(1)}
                      </div>
                    </div>
                  </Col>
                </Row>
              </div>

              {/* Status-specific alerts */}
              {isPaymentSuccessful && isBookingConfirmed ? (
                <Alert variant="success" className="mb-4">
                  <div className="d-flex align-items-center">
                    <i className="fas fa-check-circle me-2"></i>
                    <div>
                      <strong>Booking Confirmed!</strong><br />
                      You will receive a confirmation email shortly with your booking details.
                    </div>
                  </div>
                </Alert>
              ) : (
                <Alert variant="info" className="mb-4">
                  <div className="d-flex align-items-center">
                    <i className="fas fa-info-circle me-2"></i>
                    <div>
                      <strong>Payment Being Processed</strong><br />
                      Your payment is being verified. You will be notified once the process is complete.
                    </div>
                  </div>
                </Alert>
              )}

              {/* Action Buttons */}
              <div className="d-grid gap-2 d-md-block">
                <Button 
                  variant={isPaymentSuccessful ? "success" : "primary"} 
                  onClick={handleContinue}
                  className="me-md-2"
                >
                  <i className="fas fa-home me-2"></i>
                  {isPaymentSuccessful ? "Continue Browsing" : "Back to Home"}
                </Button>
                
                {!isPaymentSuccessful && (
                  <Button variant="outline-secondary" onClick={handleTryAgain}>
                    <i className="fas fa-redo me-2"></i>
                    Make Another Booking
                  </Button>
                )}
              </div>

              {/* Support Information */}
              <div className="support-info mt-4 pt-3 border-top">
                <small className="text-muted">
                  <i className="fas fa-envelope me-1"></i>
                  Need help? Contact us at support@kuantan188.com or call +60 12 345 6789
                </small>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default PaymentCallback;