import React, { useState } from 'react';
import { Modal, Button, Row, Col } from 'react-bootstrap';
import { Event } from '../../services/api';
import { format } from 'date-fns';
import Calendar from '../Calendar/Calendar';
import PaymentMethodSelector, { PaymentMethod } from './PaymentMethodSelector';
import paymentApi, { BookingData } from '../../services/paymentApi';
import PaymentLoading from '../loading/PaymentLoading';

interface ReservationModalProps {
  show: boolean;
  onHide: () => void;
  event: Event | null;
}

const ReservationModal: React.FC<ReservationModalProps> = ({ show, onHide, event }) => {
  const [adultQuantity, setAdultQuantity] = useState(2);
  const [childQuantity, setChildQuantity] = useState(0);
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [currentStep, setCurrentStep] = useState<'tickets' | 'checkout' | 'payment' | 'thankyou'>('tickets');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cash_on_delivery');
  const [isProcessingPayment, setIsProcessingPayment] = useState(false);
  
  // Contact form state
  const [contactForm, setContactForm] = useState({
    firstName: '',
    lastName: '',
    email: '',
    mobilePhone: '',
    country: '',
    postalCode: '',
    receiveUpdates: false
  });

  const handleAdultQuantityChange = (change: number) => {
    const newQuantity = adultQuantity + change;
    if (newQuantity >= 0) {
      setAdultQuantity(newQuantity);
    }
  };

  const handleChildQuantityChange = (change: number) => {
    const newQuantity = childQuantity + change;
    if (newQuantity >= 0) {
      setChildQuantity(newQuantity);
    }
  };

  const getTotalQuantity = () => {
    return adultQuantity + childQuantity;
  };

  const handlePaymentFlow = async () => {
    try {
      setIsProcessingPayment(true);
      
      const bookingData: BookingData = {
        event_id: event?.id,
        event_title: event?.title || 'Event Booking',
        customer_name: `${contactForm.firstName} ${contactForm.lastName}`,
        email: contactForm.email,
        mobile_phone: contactForm.mobilePhone,
        country: contactForm.country,
        postal_code: contactForm.postalCode,
        quantity: getTotalQuantity(),
        adult_tickets: adultQuantity,
        child_tickets: childQuantity,
        event_date: selectedDate.toISOString().split('T')[0],
        total_amount: calculateTotal(),
        payment_method: paymentMethod,
        booking_status: paymentMethod === 'billplz' ? 'pending' : 'confirmed',
        receive_updates: contactForm.receiveUpdates
      };

      if (paymentMethod === 'billplz') {
        // Process online payment
        const { bookingResponse, paymentUrl } = await paymentApi.processPayment(bookingData);
        
        // Add callback URL with booking ID
        const callbackUrl = `${window.location.origin}/payment/callback?booking_id=${bookingResponse.booking.id}`;
        const fullPaymentUrl = `${paymentUrl}&redirect_url=${encodeURIComponent(callbackUrl)}`;
        
        // Redirect to Billplz payment page
        window.location.href = fullPaymentUrl;
      } else {
        // Process cash on delivery
        await paymentApi.processCashOnDelivery(bookingData);
        setCurrentStep('thankyou');
      }
    } catch (error) {
      console.error('[PAYMENT FLOW] Error:', error);
      const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
      alert(`Payment failed: ${errorMessage}`);
    } finally {
      setIsProcessingPayment(false);
    }
  };

  const calculateTotal = () => {
    if (!event) return 1000;
    const adultPrice = typeof event.price === 'string' 
      ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
      : event.price || 500;
    const childPrice = event.child_price || (adultPrice * 0.7); // Child price is 70% of adult if not specified
    return (adultPrice * adultQuantity) + (childPrice * childQuantity);
  };

  const handleDateSelect = (date: Date) => {
    setSelectedDate(date);
  };

  const handleAddToCart = () => {
    if (getTotalQuantity() === 0) {
      alert('Please select at least one ticket.');
      return;
    }
    setCurrentStep('checkout');
  };

  const handleBack = () => {
    if (currentStep === 'checkout') {
      setCurrentStep('tickets');
    } else if (currentStep === 'payment') {
      setCurrentStep('checkout');
    }
  };

  const handleContactFormChange = (field: string, value: string | boolean) => {
    setContactForm(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleCheckout = () => {
    setCurrentStep('payment');
  };

  const handlePayment = async () => {
    await handlePaymentFlow();
  };

  const handleCloseModal = () => {
    onHide();
    setCurrentStep('tickets');
    setIsProcessingPayment(false);
    setContactForm({
      firstName: '',
      lastName: '',
      email: '',
      mobilePhone: '',
      country: '',
      postalCode: '',
      receiveUpdates: false
    });
  };

  if (!event) return null;

  const getStepTitle = () => {
    switch (currentStep) {
      case 'tickets': return 'Tickets';
      case 'checkout': return 'Checkout';
      case 'payment': return 'Payment';
      case 'thankyou': return 'Booking Confirmed';
      default: return 'Tickets';
    }
  };

  const getActionButton = () => {
    switch (currentStep) {
      case 'tickets': 
        return { text: 'Add to cart', action: handleAddToCart };
      case 'checkout': 
        return { text: 'Continue to Payment', action: handleCheckout };
      case 'payment': 
        return { text: 'Confirm Booking', action: handlePayment };
      default: 
        return { text: 'Add to cart', action: handleAddToCart };
    }
  };

  return (
    <Modal 
      show={show} 
      onHide={onHide} 
      size="lg" 
      centered
      backdrop="static"
      className="reservation-modal"
    >
      <Modal.Body className="p-0">
        <div className="modal-content-wrapper" style={{ 
          backgroundColor: '#fff', 
          borderRadius: '7px',
          overflow: 'hidden',
          minHeight: '500px'
        }}>
          {/* Header */}
          <div className="d-flex justify-content-between align-items-center p-4 border-bottom">
            <div className="d-flex align-items-center">
              {(currentStep === 'checkout' || currentStep === 'payment') && (
                <Button 
                  variant="link" 
                  className="text-dark p-0 me-3" 
                  onClick={handleBack}
                  style={{ textDecoration: 'none' }}
                >
                  <i className="fas fa-arrow-left fs-5"></i>
                </Button>
              )}
              <h5 className="mb-0 fw-bold">{getStepTitle()}</h5>
              <span className="badge text-white ms-3 px-3 py-2 rounded-pill fs-6" 
                    style={{ backgroundColor: '#ff014a' }}>
                ₹{calculateTotal().toLocaleString()}
              </span>
            </div>
            <div className="d-flex align-items-center text-muted">
              <i className="fas fa-clock me-2 text-danger"></i>
              <span>27:09</span>
              <i className="fas fa-globe ms-3 me-3"></i>
              <Button variant="link" className="text-muted ms-1 p-0" onClick={onHide}>
                <i className="fas fa-times fs-5"></i>
              </Button>
            </div>
          </div>

          {/* Content */}
          {currentStep === 'tickets' && (
            <Row className="g-0" style={{ minHeight: '400px' }}>
              {/* Left Side - Image and Details */}
              <Col md={6} className="position-relative d-flex flex-column">
                <img 
                  src={event.image_url} 
                  alt={event.title}
                  className="w-100"
                  style={{ 
                    objectFit: 'cover',
                    height: '250px'
                  }}
                  onError={(e) => {
                    const target = e.target as HTMLImageElement;
                    target.src = `https://picsum.photos/600/400?random=${event.id}`;
                  }}
                />
                
                {/* Event Title and Description under image */}
                <div className="p-3 flex-grow-1">
                  <h4 className="fw-bold mb-3" style={{ fontSize: '1.4rem' }}>
                    {event.title || "Proposal Package - Standard Package"}
                  </h4>
                  <p className="text-muted lh-base mb-4" style={{ fontSize: '0.85rem' }}>
                    {event.description || "Propose in style. 1,000 feet above the city with our exclusive package featuring a floral heart display with attached Marry Me signage, romantic LED candles, rose petals, a white carpet runner leading up to the display, and a high-top table and chairs, all set against the breathtaking backdrop of Chicago's skyline from our Merrill Semi-Private Event Space. Located on the 94th floor observation deck, this 380 sqft space offers unparalleled views of the city. Enjoy exclusivity for one hour as you pop the question in this unforgettable setting."}
                  </p>
                  
                  {/* Quantity Selectors */}
                  <div className="mb-4">
                    {/* Adult Tickets */}
                    <div className="d-flex justify-content-between align-items-center mb-3">
                      <div>
                        <div className="fw-bold mb-1" style={{ fontSize: '1rem' }}>Adult</div>
                        <small className="text-muted" style={{ fontSize: '0.8rem' }}>
                          from ${typeof event.price === 'string' 
                            ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
                            : event.price || 500}
                        </small>
                      </div>
                      <div className="d-flex align-items-center">
                        <Button 
                          variant="outline-success"
                          className="rounded-circle d-flex align-items-center justify-content-center border-2"
                          style={{ 
                            width: '36px', 
                            height: '36px',
                            borderColor: '#1a0007',
                            color: '#1a0007',
                            fontSize: '18px'
                          }}
                          onClick={() => handleAdultQuantityChange(-1)}
                          disabled={adultQuantity <= 0}
                        >
                          −
                        </Button>
                        <span className="mx-4 fw-bold" style={{ fontSize: '1.2rem' }}>
                          {adultQuantity}
                        </span>
                        <Button 
                          variant="outline-success"
                          className="rounded-circle d-flex align-items-center justify-content-center border-2"
                          style={{ 
                            width: '36px', 
                            height: '36px',
                            borderColor: '#1a0007',
                            color: '#1a0007',
                            fontSize: '16px'
                          }}
                          onClick={() => handleAdultQuantityChange(1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                    
                    {/* Child Tickets */}
                    <div className="d-flex justify-content-between align-items-center">
                      <div>
                        <div className="fw-bold mb-1" style={{ fontSize: '1rem' }}>Child</div>
                        <small className="text-muted" style={{ fontSize: '0.8rem' }}>
                          from ${event.child_price || Math.round((typeof event.price === 'string' 
                            ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
                            : event.price || 500) * 0.7)}
                        </small>
                      </div>
                      <div className="d-flex align-items-center">
                        <Button 
                          variant="outline-success"
                          className="rounded-circle d-flex align-items-center justify-content-center border-2"
                          style={{ 
                            width: '36px', 
                            height: '36px',
                            borderColor: '#1a0007',
                            color: '#1a0007',
                            fontSize: '18px'
                          }}
                          onClick={() => handleChildQuantityChange(-1)}
                          disabled={childQuantity <= 0}
                        >
                          −
                        </Button>
                        <span className="mx-4 fw-bold" style={{ fontSize: '1.2rem' }}>
                          {childQuantity}
                        </span>
                        <Button 
                          variant="outline-success"
                          className="rounded-circle d-flex align-items-center justify-content-center border-2"
                          style={{ 
                            width: '36px', 
                            height: '36px',
                            borderColor: '#1a0007',
                            color: '#1a0007',
                            fontSize: '16px'
                          }}
                          onClick={() => handleChildQuantityChange(1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </Col>

              {/* Right Side - Content */}
              <Col md={6} className="p-4 d-flex flex-column">
                {/* Date Selection */}
                <div className="mb-4">
                  <label className="fw-semibold mb-2 d-block" style={{ fontSize: '0.9rem' }}>
                    Select Date
                  </label>
                  <div className="alert alert-light border-0 p-3 mb-3" 
                       style={{ backgroundColor: '#f8f9fa', fontSize: '0.8rem' }}>
                    <span className="text-muted">
                      <i className="fas fa-calendar-check me-2" style={{ color: '#00c851' }}></i>
                      Selected: {format(selectedDate, 'PPP')}
                    </span>
                  </div>
                  
                  {/* Calendar Component */}
                  <Calendar 
                    selectedDate={selectedDate}
                    onDateSelect={handleDateSelect}
                    minDate={new Date()}
                    disabledDates={[
                      new Date(), // Today is fully booked
                      new Date(Date.now() + 86400000) // Tomorrow is fully booked
                    ]}
                  />
                </div>
              </Col>
            </Row>
          )}

          {currentStep === 'checkout' && (
            <div className="p-4" style={{ minHeight: '400px' }}>
              <Row>
                <Col md={4}>
                  <div className="border rounded-3 p-3 mb-4">
                    <h6 className="fw-bold mb-3">Order Summary</h6>
                    <div className="d-flex mb-2">
                      <img 
                        src={event.image_url} 
                        alt={event.title}
                        className="rounded"
                        style={{ width: '60px', height: '45px', objectFit: 'cover' }}
                        onError={(e) => {
                          const target = e.target as HTMLImageElement;
                          target.src = `https://picsum.photos/600/400?random=${event.id}`;
                        }}
                      />
                      <div className="ms-3 flex-grow-1">
                        <div className="fw-semibold" style={{ fontSize: '0.9rem' }}>
                          {event.title}
                        </div>
                        <small className="text-muted">
                          {format(selectedDate, 'MMM dd, yyyy')}
                        </small>
                      </div>
                    </div>
                    <hr className="my-3" />
                    {adultQuantity > 0 && (
                      <div className="d-flex justify-content-between mb-2">
                        <span>Adult Tickets ({adultQuantity})</span>
                        <span>₹{(adultQuantity * (typeof event.price === 'string' 
                          ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
                          : event.price || 500)).toLocaleString()}</span>
                      </div>
                    )}
                    {childQuantity > 0 && (
                      <div className="d-flex justify-content-between mb-2">
                        <span>Child Tickets ({childQuantity})</span>
                        <span>₹{(childQuantity * (event.child_price || Math.round((typeof event.price === 'string' 
                          ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
                          : event.price || 500) * 0.7))).toLocaleString()}</span>
                      </div>
                    )}
                    <div className="d-flex justify-content-between mb-2">
                      <span>Taxes & Fees</span>
                      <span>₹0</span>
                    </div>
                    <hr className="my-3" />
                    <div className="d-flex justify-content-between fw-bold">
                      <span>Total</span>
                      <span>₹{calculateTotal().toLocaleString()}</span>
                    </div>
                  </div>
                </Col>

                <Col md={8}>
                  <div className="checkout-form">
                    <h6 className="fw-bold mb-3">Contact Details</h6>
                    <p className="text-muted mb-1">* Required Fields</p>
                    
                    <form className="mt-4">
                      <Row>
                        <Col md={6} className="mb-3">
                          <label className="form-label fw-semibold">
                            First name <span className="text-danger">*</span>
                          </label>
                          <input
                            type="text"
                            className="form-control"
                            value={contactForm.firstName}
                            onChange={(e) => handleContactFormChange('firstName', e.target.value)}
                            required
                          />
                        </Col>

                        <Col md={6} className="mb-3">
                          <label className="form-label fw-semibold">
                            Last name <span className="text-danger">*</span>
                          </label>
                          <input
                            type="text"
                            className="form-control"
                            value={contactForm.lastName}
                            onChange={(e) => handleContactFormChange('lastName', e.target.value)}
                            required
                          />
                        </Col>
                      </Row>

                      <div className="mb-3">
                        <label className="form-label fw-semibold">
                          Email <span className="text-danger">*</span>
                        </label>
                        <input
                          type="email"
                          className="form-control"
                          value={contactForm.email}
                          onChange={(e) => handleContactFormChange('email', e.target.value)}
                          required
                        />
                      </div>

                      <div className="mb-3">
                        <label className="form-label fw-semibold">Mobile phone</label>
                        <input
                          type="tel"
                          className="form-control"
                          value={contactForm.mobilePhone}
                          onChange={(e) => handleContactFormChange('mobilePhone', e.target.value)}
                        />
                      </div>

                      <Row>
                        <Col md={6} className="mb-3">
                          <label className="form-label fw-semibold">
                            Country <span className="text-danger">*</span>
                          </label>
                          <select
                            className="form-select"
                            value={contactForm.country}
                            onChange={(e) => handleContactFormChange('country', e.target.value)}
                            required
                          >
                            <option value="">Select country</option>
                            <option value="MY">Malaysia</option>
                            <option value="SG">Singapore</option>
                            <option value="TH">Thailand</option>
                            <option value="ID">Indonesia</option>
                            <option value="PH">Philippines</option>
                            <option value="VN">Vietnam</option>
                            <option value="IN">India</option>
                            <option value="CN">China</option>
                            <option value="JP">Japan</option>
                            <option value="KR">South Korea</option>
                            <option value="AU">Australia</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="CA">Canada</option>
                            <option value="DE">Germany</option>
                            <option value="FR">France</option>
                            <option value="IT">Italy</option>
                            <option value="ES">Spain</option>
                            <option value="NL">Netherlands</option>
                            <option value="AE">UAE</option>
                          </select>
                        </Col>

                        <Col md={6} className="mb-3">
                          <label className="form-label fw-semibold">
                            ZIP / Postal Code <span className="text-danger">*</span>
                          </label>
                          <input
                            type="text"
                            className="form-control"
                            value={contactForm.postalCode}
                            onChange={(e) => handleContactFormChange('postalCode', e.target.value)}
                            required
                          />
                        </Col>
                      </Row>

                      <div className="mb-4">
                        <div className="form-check">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            id="receiveUpdates"
                            checked={contactForm.receiveUpdates}
                            onChange={(e) => handleContactFormChange('receiveUpdates', e.target.checked)}
                          />
                          <label className="form-check-label" htmlFor="receiveUpdates">
                            I would like kuantan 188 to send me exclusive updates and the latest offers.
                          </label>
                        </div>
                      </div>
                    </form>
                  </div>
                </Col>
              </Row>
            </div>
          )}

          {currentStep === 'payment' && (
            <div className="p-4" style={{ minHeight: '400px' }}>
              <Row>
                <Col md={4}>
                  <div className="border rounded-3 p-3 mb-4">
                    <h6 className="fw-bold mb-3">Order Summary</h6>
                    <div className="d-flex mb-2">
                      <img 
                        src={event.image_url} 
                        alt={event.title}
                        className="rounded"
                        style={{ width: '60px', height: '45px', objectFit: 'cover' }}
                        onError={(e) => {
                          const target = e.target as HTMLImageElement;
                          target.src = `https://picsum.photos/600/400?random=${event.id}`;
                        }}
                      />
                      <div className="ms-3 flex-grow-1">
                        <div className="fw-semibold" style={{ fontSize: '0.9rem' }}>
                          {event.title}
                        </div>
                        <small className="text-muted">
                          {format(selectedDate, 'MMM dd, yyyy')}
                        </small>
                      </div>
                    </div>
                    <hr className="my-3" />
                    {adultQuantity > 0 && (
                      <div className="d-flex justify-content-between mb-2">
                        <span>Adult Tickets ({adultQuantity})</span>
                        <span>₹{(adultQuantity * (typeof event.price === 'string' 
                          ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
                          : event.price || 500)).toLocaleString()}</span>
                      </div>
                    )}
                    {childQuantity > 0 && (
                      <div className="d-flex justify-content-between mb-2">
                        <span>Child Tickets ({childQuantity})</span>
                        <span>₹{(childQuantity * (event.child_price || Math.round((typeof event.price === 'string' 
                          ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
                          : event.price || 500) * 0.7))).toLocaleString()}</span>
                      </div>
                    )}
                    <div className="d-flex justify-content-between mb-2">
                      <span>Taxes & Fees</span>
                      <span>₹0</span>
                    </div>
                    <hr className="my-3" />
                    <div className="d-flex justify-content-between fw-bold">
                      <span>Total</span>
                      <span>₹{calculateTotal().toLocaleString()}</span>
                    </div>

                    <div className="mt-4 pt-3 border-top">
                      <h6 className="fw-bold mb-2">Customer Details</h6>
                      <div className="small text-muted">
                        <div>{contactForm.firstName} {contactForm.lastName}</div>
                        <div>{contactForm.email}</div>
                        {contactForm.mobilePhone && <div>{contactForm.mobilePhone}</div>}
                        <div>{contactForm.country}</div>
                      </div>
                    </div>
                  </div>
                </Col>

                <Col md={8}>
                  <div className="payment-section">
                    <PaymentMethodSelector
                      selectedMethod={paymentMethod}
                      onMethodChange={setPaymentMethod}
                      totalAmount={calculateTotal()}
                      currency="₹"
                    />

                    <div className="border rounded-3 p-3 mt-4" style={{ backgroundColor: '#f8f9fa' }}>
                      <div className="form-check">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          id="acceptTerms"
                          required
                        />
                        <label className="form-check-label small" htmlFor="acceptTerms">
                          I agree to the Terms & Conditions and Cancellation Policy
                        </label>
                      </div>
                    </div>
                  </div>
                </Col>
              </Row>
            </div>
          )}

          {currentStep === 'thankyou' && (
            <div className="p-5 text-center" style={{ minHeight: '400px' }}>
              <div className="mb-4">
                <i className="fas fa-check-circle text-success" style={{ fontSize: '4rem' }}></i>
              </div>
              
              <h3 className="fw-bold text-success mb-3">Thank You!</h3>
              <h5 className="mb-4">Your booking has been confirmed</h5>
              
              <div className="alert alert-success border-0 mb-4" style={{ backgroundColor: '#d1edff' }}>
                <div className="fw-semibold mb-2">Booking Details</div>
                <div className="row text-start small">
                  <div className="col-6">
                    <strong>Event:</strong> {event.title}
                  </div>
                  <div className="col-6">
                    <strong>Date:</strong> {format(selectedDate, 'PPP')}
                  </div>
                  <div className="col-6 mt-2">
                    <strong>Tickets:</strong> {adultQuantity > 0 && `${adultQuantity} Adult${adultQuantity > 1 ? 's' : ''}`}{childQuantity > 0 && (adultQuantity > 0 ? `, ${childQuantity} Child${childQuantity > 1 ? 'ren' : ''}` : `${childQuantity} Child${childQuantity > 1 ? 'ren' : ''}`)}
                  </div>
                  <div className="col-6 mt-2">
                    <strong>Total:</strong> ₹{calculateTotal().toLocaleString()}
                  </div>
                  <div className="col-12 mt-2">
                    <strong>Customer:</strong> {contactForm.firstName} {contactForm.lastName}
                  </div>
                  <div className="col-12 mt-1">
                    <strong>Email:</strong> {contactForm.email}
                  </div>
                  <div className="col-12 mt-1">
                    <strong>Payment:</strong> {paymentMethod === 'cash_on_delivery' ? 'Cash on Delivery' : 'Billplz Online Payment'}
                  </div>
                </div>
              </div>
              
              <div className="alert alert-info border-0 mb-4">
                <div className="d-flex align-items-center justify-content-center">
                  <i className="fas fa-info-circle text-primary me-2"></i>
                  <div className="small text-start">
                    <strong>What's next?</strong>
                    <ul className="mb-0 mt-1">
                      <li>A confirmation email has been sent to {contactForm.email}</li>
                      <li>Arrive at the venue on {format(selectedDate, 'PPP')}</li>
                      <li>Bring valid ID and cash payment (₹{calculateTotal().toLocaleString()})</li>
                      <li>Contact us if you need to make any changes</li>
                    </ul>
                  </div>
                </div>
              </div>
              
              <div className="mb-3">
                <small className="text-muted">
                  Booking reference will be sent to your email shortly
                </small>
              </div>
            </div>
          )}

          {/* Footer Buttons */}
          {currentStep !== 'thankyou' && (
            <div className="px-4 pt-3 pb-3 d-flex justify-content-between" style={{ backgroundColor: '#BAA73F' }}>
              {(currentStep === 'checkout' || currentStep === 'payment') && (
                <Button 
                  variant="outline-dark"
                  className="fw-bold py-2 px-4 rounded-3"
                  onClick={onHide}
                  style={{
                    borderColor: '#000000',
                    color: '#000000',
                    fontSize: '0.9rem'
                  }}
                >
                  <i className="fas fa-shopping-bag me-2"></i>
                  Continue Shopping
                </Button>
              )}
              <div className={currentStep === 'tickets' ? 'w-100 d-flex justify-content-end' : ''}>
                <Button 
                  className="fw-bold py-2 px-4 rounded-3 border-0"
                  onClick={getActionButton().action}
                  style={{
                    backgroundColor: '#000000 !important',
                    color: '#fff !important',
                    fontSize: '0.9rem'
                  }}
                >
                  {getActionButton().text}
                  <i className="fas fa-arrow-right ms-2"></i>
                </Button>
              </div>
            </div>
          )}
          
          {/* Thank You Footer */}
          {currentStep === 'thankyou' && (
            <div className="px-4 pt-3 pb-3 d-flex justify-content-center" style={{ backgroundColor: '#BAA73F' }}>
              <Button 
                className="fw-bold py-2 px-4 rounded-3 border-0"
                onClick={handleCloseModal}
                style={{
                  backgroundColor: '#000000 !important',
                  color: '#fff !important',
                  fontSize: '0.9rem'
                }}
              >
                <i className="fas fa-home me-2"></i>
                Back to Events
              </Button>
            </div>
          )}
        </div>
      </Modal.Body>
      
      {/* Payment Loading Modal */}
      <PaymentLoading 
        show={isProcessingPayment}
        title={paymentMethod === 'billplz' ? "Creating Payment Link" : "Processing Booking"}
        message={paymentMethod === 'billplz' 
          ? "Please wait while we create your secure payment link..."
          : "Please wait while we process your booking..."
        }
      />
    </Modal>
  );
};

export default ReservationModal;