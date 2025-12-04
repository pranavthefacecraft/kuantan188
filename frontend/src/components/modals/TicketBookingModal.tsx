import React, { useState } from 'react';
import { Modal, Button, Row, Col, Form } from 'react-bootstrap';
import { format, addDays } from 'date-fns';

interface Ticket {
  id: number;
  title?: string;
  name?: string;
  description?: string;
  adult_price?: string;
  child_price?: string;
  price?: string;
  image_url?: string;
  total_quantity?: number;
  available_quantity?: number;
  countries?: Array<{
    id: number;
    name: string;
    currency_symbol: string;
    adult_price: string;
    child_price: string;
  }>;
}

interface TicketBookingModalProps {
  show: boolean;
  onHide: () => void;
  ticket: Ticket | null;
}

const TicketBookingModal: React.FC<TicketBookingModalProps> = ({ show, onHide, ticket }) => {
  const [currentStep, setCurrentStep] = useState<'selection' | 'details' | 'payment' | 'thankyou'>('selection');
  const [selectedCountry, setSelectedCountry] = useState<any>(null);
  const [adultQuantity, setAdultQuantity] = useState(1);
  const [childQuantity, setChildQuantity] = useState(0);
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [showCalendar, setShowCalendar] = useState(false);
  
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

  // Reset modal state when it opens/closes
  React.useEffect(() => {
    if (show && ticket) {
      setCurrentStep('selection');
      setSelectedCountry(ticket.countries?.[0] || null);
      setAdultQuantity(1);
      setChildQuantity(0);
      setSelectedDate(new Date());
      setSelectedTime('');
      setShowCalendar(false);
      setContactForm({
        firstName: '',
        lastName: '',
        email: '',
        mobilePhone: '',
        country: '',
        postalCode: '',
        receiveUpdates: false
      });
    }
  }, [show, ticket]);

  const handleQuantityChange = (type: 'adult' | 'child', change: number) => {
    if (type === 'adult') {
      const newQuantity = adultQuantity + change;
      if (newQuantity >= 0) {
        setAdultQuantity(newQuantity);
      }
    } else {
      const newQuantity = childQuantity + change;
      if (newQuantity >= 0) {
        setChildQuantity(newQuantity);
      }
    }
  };

  const calculateTotal = () => {
    if (!selectedCountry) return 0;
    
    const adultPrice = parseFloat(selectedCountry.adult_price || '0');
    const childPrice = parseFloat(selectedCountry.child_price || '0');
    
    return (adultPrice * adultQuantity) + (childPrice * childQuantity);
  };

  const getTotalQuantity = () => {
    return adultQuantity + childQuantity;
  };

  const handleContinueToDetails = () => {
    if (getTotalQuantity() === 0) {
      alert('Please select at least one ticket');
      return;
    }
    if (!selectedTime) {
      alert('Please select a time slot');
      return;
    }
    setCurrentStep('details');
  };

  const handleContinueToPayment = () => {
    // Validate contact form
    if (!contactForm.firstName || !contactForm.lastName || !contactForm.email || !contactForm.mobilePhone) {
      alert('Please fill in all required fields');
      return;
    }
    setCurrentStep('payment');
  };

  const handlePayment = () => {
    // Simulate payment processing
    setTimeout(() => {
      setCurrentStep('thankyou');
    }, 2000);
  };

  const handleClose = () => {
    setCurrentStep('selection');
    onHide();
  };

  if (!ticket) return null;

  const ticketName = ticket.title || ticket.name || 'Ticket';

  return (
    <Modal 
      show={show} 
      onHide={handleClose}
      size="xl"
      backdrop="static"
      keyboard={false}
      className="ticket-booking-modal"
    >
      <Modal.Header closeButton className="modal-header-custom">
        <Modal.Title className="modal-title-custom">
          {currentStep === 'selection' && 'Tickets'}
          {currentStep === 'details' && (
            <div className="d-flex align-items-center">
              <i className="fas fa-arrow-left me-3" style={{cursor: 'pointer', color: '#666'}} onClick={() => setCurrentStep('selection')}></i>
              Checkout
            </div>
          )}
          {currentStep === 'payment' && 'Payment'}
          {currentStep === 'thankyou' && 'Booking Confirmed'}
        </Modal.Title>
      </Modal.Header>
      
      <Modal.Body className="modal-body-custom">
        {currentStep === 'selection' && (
          <div className="ticket-selection-step">
            <Row>
              {/* Left Side - Ticket Image and Info */}
              <Col md={5}>
                <div className="ticket-info">
                  {ticket.image_url && (
                    <div className="ticket-image-container mb-3">
                      <img 
                        src={ticket.image_url} 
                        alt={ticketName}
                        className="img-fluid rounded"
                        style={{ width: '100%', height: '180px', objectFit: 'cover' }}
                      />
                    </div>
                  )}
                  <div className="ticket-content">
                    <h4 className="ticket-title mb-2">{ticketName}</h4>
                    {ticket.description && (
                      <p className="ticket-description text-muted small mb-3">{ticket.description}</p>
                    )}
                  </div>
                  
                  {/* Adult Quantity */}
                  <div className="quantity-section mb-3">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <span className="fw-bold">Adult</span>
                        <div className="small text-muted">
                          from {selectedCountry?.currency_symbol || '$'}{selectedCountry ? parseFloat(selectedCountry.adult_price).toFixed(0) : '49'}
                        </div>
                      </div>
                      <div className="quantity-controls d-flex align-items-center">
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('adult', -1)}
                          disabled={adultQuantity === 0}
                        >
                          −
                        </Button>
                        <span className="quantity-display mx-3">{adultQuantity}</span>
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('adult', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>

                  {/* Child Quantity */}
                  <div className="quantity-section mb-4">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <span className="fw-bold">Child</span>
                        <div className="small text-muted">
                          from {selectedCountry?.currency_symbol || '$'}{selectedCountry ? parseFloat(selectedCountry.child_price).toFixed(0) : '35'}
                        </div>
                      </div>
                      <div className="quantity-controls d-flex align-items-center">
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('child', -1)}
                          disabled={childQuantity === 0}
                        >
                          −
                        </Button>
                        <span className="quantity-display mx-3">{childQuantity}</span>
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('child', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </Col>

              {/* Right Side - Date and Time Selection */}
              <Col md={7}>
                <div className="booking-options">
                  {/* Country Selection */}
                  {ticket.countries && ticket.countries.length > 1 && (
                    <div className="mb-4">
                      <h6 className="mb-3">Select Country/Region</h6>
                      <Form.Select
                        value={selectedCountry?.id || ''}
                        onChange={(e) => {
                          const country = ticket.countries?.find(c => c.id === parseInt(e.target.value));
                          setSelectedCountry(country);
                        }}
                        className="country-select"
                      >
                        {ticket.countries.map((country) => (
                          <option key={country.id} value={country.id}>
                            {country.name} ({country.currency_symbol})
                          </option>
                        ))}
                      </Form.Select>
                    </div>
                  )}

                  {/* Date Selection */}
                  <div className="mb-4">
                    <h6 className="mb-3">Select Date</h6>
                    <div className="date-options d-flex gap-2">
                      {[0, 1].map((dayOffset) => {
                        const date = addDays(new Date(), dayOffset);
                        const isSelected = format(selectedDate, 'yyyy-MM-dd') === format(date, 'yyyy-MM-dd');
                        const isToday = dayOffset === 0;
                        
                        return (
                          <button
                            key={dayOffset}
                            className={`date-option ${isSelected ? 'selected' : ''}`}
                            onClick={() => {
                              setSelectedDate(date);
                              setShowCalendar(false);
                            }}
                            type="button"
                          >
                            <div className="date-number">{format(date, 'd')}</div>
                            <div className="date-label">
                              {isToday ? 'Today' : 'Tomorrow'}
                            </div>
                            <div className="date-price">
                              {selectedCountry?.currency_symbol || '$'}{selectedCountry ? parseFloat(selectedCountry.adult_price).toFixed(0) : '49'}
                            </div>
                          </button>
                        );
                      })}
                      
                      {/* Other Dates - Calendar Trigger or Selected Custom Date */}
                      {(() => {
                        const today = new Date();
                        const tomorrow = addDays(new Date(), 1);
                        const isCustomDate = format(selectedDate, 'yyyy-MM-dd') !== format(today, 'yyyy-MM-dd') && 
                                           format(selectedDate, 'yyyy-MM-dd') !== format(tomorrow, 'yyyy-MM-dd');
                        
                        return (
                          <button
                            className={`date-option other-dates ${isCustomDate ? 'selected' : ''}`}
                            onClick={() => setShowCalendar(!showCalendar)}
                            type="button"
                          >
                            {isCustomDate ? (
                              <>
                                <div className="date-number">{format(selectedDate, 'd')}</div>
                                <div className="date-label">{format(selectedDate, 'MMM')}</div>
                              </>
                            ) : (
                              <>
                                <div className="date-icon">📅</div>
                                <div className="date-label">Other Dates</div>
                              </>
                            )}
                            <div className="date-price">
                              {selectedCountry?.currency_symbol || '$'}{selectedCountry ? parseFloat(selectedCountry.adult_price).toFixed(0) : '49'}
                            </div>
                          </button>
                        );
                      })()}
                    </div>

                    {/* Inline Calendar */}
                    {showCalendar && (
                      <div className="inline-calendar-container mt-3">
                        <div className="calendar-header d-flex justify-content-between align-items-center mb-3">
                          <Button variant="link" className="p-0 text-muted">
                            <i className="fas fa-chevron-left"></i>
                          </Button>
                          <h6 className="mb-0 fw-bold">December 2025</h6>
                          <Button variant="link" className="p-0 text-muted">
                            <i className="fas fa-chevron-right"></i>
                          </Button>
                        </div>
                        
                        <div className="calendar-grid">
                          <div className="calendar-weekdays mb-2">
                            {['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'].map(day => (
                              <div key={day} className="weekday text-muted text-center small">{day}</div>
                            ))}
                          </div>
                          
                          <div className="calendar-days">
                            {Array.from({length: 31}, (_, i) => i + 1).map(day => {
                              const isToday = day === 4;
                              const isTomorrow = day === 5;
                              const isSelected = day === 12;
                              
                              return (
                                <div 
                                  key={day}
                                  className={`calendar-day ${isToday || isTomorrow ? 'highlight' : ''} ${isSelected ? 'selected' : ''}`}
                                  onClick={() => {
                                    const newDate = new Date(2025, 11, day); // December 2025
                                    setSelectedDate(newDate);
                                    setShowCalendar(false);
                                  }}
                                >
                                  <div className="day-number">{day}</div>
                                  <div className="day-price small">$49</div>
                                </div>
                              );
                            })}
                          </div>
                        </div>

                        {/* Time Slots in Calendar */}
                        <div className="calendar-time-slots mt-3 pt-3 border-top">
                          <div className="row g-2">
                            <div className="col-6">
                              <Button 
                                variant="outline-secondary" 
                                size="sm"
                                className="w-100 d-flex justify-content-between"
                                onClick={() => {
                                  setSelectedTime('15:00');
                                  setShowCalendar(false);
                                }}
                              >
                                <span>15:00</span>
                                <span className="text-muted">$49</span>
                              </Button>
                            </div>
                            <div className="col-6">
                              <Button 
                                variant="outline-secondary" 
                                size="sm"
                                className="w-100 d-flex justify-content-between"
                                onClick={() => {
                                  setSelectedTime('15:30');
                                  setShowCalendar(false);
                                }}
                              >
                                <span>15:30</span>
                                <span className="text-muted">$49</span>
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    )}
                  </div>

                  {/* Time Selection */}
                  <div>
                    <h6 className="mb-3">Select time</h6>
                    <div className="time-slots">
                      {['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30'].map((time, index) => {
                        const isBestPrice = index < 4; // First 4 slots are "best price"
                        const price = selectedCountry ? parseFloat(selectedCountry.adult_price) + (isBestPrice ? 0 : 5) : (44 + (isBestPrice ? 0 : 5));
                        
                        return (
                          <button
                            key={time}
                            className={`time-slot ${selectedTime === time ? 'selected' : ''} ${isBestPrice ? 'best-price' : ''}`}
                            onClick={() => setSelectedTime(time)}
                            type="button"
                          >
                            <div className="time-label">{time}</div>
                            <div className="time-price">
                              {selectedCountry?.currency_symbol || '$'}{price}
                            </div>
                            {isBestPrice && <div className="best-price-badge">Best price</div>}
                          </button>
                        );
                      })}
                    </div>
                  </div>
                </div>
              </Col>
            </Row>
          </div>
        )}

        {currentStep === 'details' && (
          <div className="checkout-step">
            <Row>
              {/* Left Side - Contact Details */}
              <Col md={7}>
                <div className="contact-details">
                  <h5 className="mb-3">Contact Details</h5>
                  <p className="text-muted mb-4"><span className="text-danger">*</span> Required Fields</p>
                  
                  <Form.Group className="mb-3">
                    <Form.Label>First name <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="text"
                      value={contactForm.firstName}
                      onChange={(e) => setContactForm({...contactForm, firstName: e.target.value})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Last name <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="text"
                      value={contactForm.lastName}
                      onChange={(e) => setContactForm({...contactForm, lastName: e.target.value})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Email <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="email"
                      value={contactForm.email}
                      onChange={(e) => setContactForm({...contactForm, email: e.target.value})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Mobile phone</Form.Label>
                    <Form.Control
                      type="tel"
                      value={contactForm.mobilePhone}
                      onChange={(e) => setContactForm({...contactForm, mobilePhone: e.target.value})}
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Country <span className="text-danger">*</span></Form.Label>
                    <Form.Select
                      value={contactForm.country}
                      onChange={(e) => setContactForm({...contactForm, country: e.target.value})}
                      required
                    >
                      <option value="">Select country</option>
                      <option value="Malaysia">Malaysia</option>
                      <option value="Singapore">Singapore</option>
                      <option value="United States">United States</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="Australia">Australia</option>
                      <option value="India">India</option>
                    </Form.Select>
                  </Form.Group>

                  <Form.Group className="mb-4">
                    <Form.Label>ZIP / Postal Code <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="text"
                      value={contactForm.postalCode}
                      onChange={(e) => setContactForm({...contactForm, postalCode: e.target.value})}
                      required
                    />
                  </Form.Group>

                  {/* Terms and Newsletter */}
                  <Form.Group className="mb-3">
                    <Form.Check
                      type="checkbox"
                      id="terms-checkbox"
                      label={
                        <span>
                          I agree to the <button type="button" className="btn btn-link p-0 text-primary" style={{textDecoration: 'underline', fontSize: 'inherit'}}>booking terms</button> needed to complete the order <span className="text-danger">*</span>
                        </span>
                      }
                      checked={contactForm.receiveUpdates}
                      onChange={(e) => setContactForm({...contactForm, receiveUpdates: e.target.checked})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Check
                      type="checkbox"
                      id="newsletter-checkbox"
                      label="I would like Kuantan 188 to send me exclusive updates and the latest offers."
                      checked={contactForm.receiveUpdates}
                      onChange={(e) => setContactForm({...contactForm, receiveUpdates: e.target.checked})}
                    />
                  </Form.Group>
                </div>
              </Col>

              {/* Right Side - Order Summary */}
              <Col md={5}>
                <div className="order-summary-card">
                  <div className="ticket-card mb-4">
                    {ticket.image_url && (
                      <img 
                        src={ticket.image_url} 
                        alt={ticketName}
                        className="ticket-image"
                      />
                    )}
                    <div className="ticket-info">
                      <h6 className="ticket-title">360 CHICAGO {ticketName}</h6>
                      <p className="ticket-subtitle">{ticketName}</p>
                      <p className="ticket-datetime">{format(selectedDate, 'd MMMM yyyy HH:mm')}</p>
                      
                      <div className="ticket-quantity">
                        {adultQuantity > 0 && (
                          <div className="d-flex justify-content-between align-items-center">
                            <span className="text-success">{adultQuantity}× Adult</span>
                            <span>{selectedCountry?.currency_symbol || '$'}{(parseFloat(selectedCountry?.adult_price || '0') * adultQuantity).toFixed(0)}</span>
                          </div>
                        )}
                        {childQuantity > 0 && (
                          <div className="d-flex justify-content-between align-items-center mt-1">
                            <span className="text-success">{childQuantity}× Child</span>
                            <span>{selectedCountry?.currency_symbol || '$'}{(parseFloat(selectedCountry?.child_price || '0') * childQuantity).toFixed(0)}</span>
                          </div>
                        )}
                      </div>
                      
                      <div className="ticket-actions mt-3">
                        <Button variant="link" className="p-0 text-success me-3" size="sm">Edit</Button>
                        <Button variant="link" className="p-0 text-success" size="sm">Remove</Button>
                      </div>
                    </div>
                  </div>

                  <Button className="continue-shopping-btn w-100 mb-4" variant="success" size="sm">
                    + Continue shopping
                  </Button>

                  {/* Pricing Breakdown */}
                  <div className="pricing-breakdown">
                    <div className="d-flex justify-content-between mb-2">
                      <span>Subtotal</span>
                      <span>{selectedCountry?.currency_symbol || '$'}{calculateTotal().toFixed(2)}</span>
                    </div>
                    <div className="d-flex justify-content-between mb-2">
                      <span>Amusement Tax</span>
                      <span>{selectedCountry?.currency_symbol || '$'}{(calculateTotal() * 0.05).toFixed(2)}</span>
                    </div>
                    <div className="d-flex justify-content-between mb-3">
                      <span>Bar Tax</span>
                      <span>{selectedCountry?.currency_symbol || '$'}{(calculateTotal() * 0.03).toFixed(2)}</span>
                    </div>
                    
                    <hr />
                    
                    <div className="d-flex justify-content-between total-due">
                      <strong>Total Due</strong>
                      <strong>{selectedCountry?.currency_symbol || '$'}{(calculateTotal() * 1.08).toFixed(2)}</strong>
                    </div>

                    <div className="promo-section mt-3">
                      <Button variant="link" className="p-0 text-success">
                        <i className="fas fa-plus-circle me-2"></i>
                        Enter promo / gift card code
                      </Button>
                    </div>
                  </div>
                </div>
              </Col>
            </Row>
          </div>
        )}

        {currentStep === 'payment' && (
          <div className="payment-step">
            <h4 className="mb-4 text-center">Payment Method</h4>
            <div className="payment-options">
              <div className="payment-method-card p-4 border rounded bg-light">
                <div className="d-flex align-items-center">
                  <div className="payment-icon me-3">
                    <i className="fas fa-money-bill-wave text-success" style={{ fontSize: '2rem' }}></i>
                  </div>
                  <div className="flex-grow-1">
                    <h5 className="mb-2 text-success">Cash on Delivery</h5>
                    <p className="text-muted mb-2">Pay with cash when you receive your tickets</p>
                    <small className="text-muted">
                      • No advance payment required<br/>
                      • Pay the exact amount upon delivery<br/>
                      • Secure and convenient
                    </small>
                  </div>
                  <div className="payment-check">
                    <i className="fas fa-check-circle text-success" style={{ fontSize: '1.5rem' }}></i>
                  </div>
                </div>
              </div>
              
              <div className="payment-summary mt-4 p-3 bg-white border rounded">
                <h6 className="text-center mb-3">Order Summary</h6>
                <div className="d-flex justify-content-between mb-2">
                  <span>Subtotal:</span>
                  <span>{selectedCountry?.currency_symbol || '$'}{calculateTotal().toFixed(2)}</span>
                </div>
                <div className="d-flex justify-content-between mb-2">
                  <span>Delivery Fee:</span>
                  <span className="text-success">FREE</span>
                </div>
                <hr />
                <div className="d-flex justify-content-between">
                  <strong>Total Amount (Cash on Delivery):</strong>
                  <strong className="text-success">{selectedCountry?.currency_symbol || '$'}{calculateTotal().toFixed(2)}</strong>
                </div>
              </div>
            </div>
          </div>
        )}

        {currentStep === 'thankyou' && (
          <div className="thankyou-step text-center">
            <div className="success-icon mb-3">
              <i className="fas fa-check-circle text-success" style={{ fontSize: '4rem' }}></i>
            </div>
            <h4 className="text-success mb-3">Booking Confirmed!</h4>
            <p>Thank you for your purchase. Your ticket confirmation has been sent to {contactForm.email}</p>
            <div className="booking-details mt-4 p-3 bg-light rounded">
              <h6>Booking Details:</h6>
              <p><strong>Ticket:</strong> {ticketName}</p>
              <p><strong>Date & Time:</strong> {format(selectedDate, 'MMMM d, yyyy')} at {selectedTime}</p>
              <div className="mb-2">
                <strong>Tickets:</strong>
                {adultQuantity > 0 && <div className="ms-2">• Adult × {adultQuantity}</div>}
                {childQuantity > 0 && <div className="ms-2">• Child × {childQuantity}</div>}
              </div>
              <p><strong>Total Paid:</strong> {selectedCountry?.currency_symbol}{calculateTotal().toFixed(2)}</p>
            </div>
          </div>
        )}
      </Modal.Body>
      
      <Modal.Footer className="modal-footer-custom">
        {currentStep === 'selection' && (
          <Button 
            className="continue-button w-100"
            onClick={handleContinueToDetails}
            disabled={getTotalQuantity() === 0 || !selectedTime}
          >
            Continue →
          </Button>
        )}
        
        {currentStep === 'details' && (
          <>
            <Button variant="secondary" onClick={() => setCurrentStep('selection')}>
              Back
            </Button>
            <Button variant="primary" onClick={handleContinueToPayment}>
              Continue to Payment
            </Button>
          </>
        )}
        
        {currentStep === 'payment' && (
          <>
            <Button variant="secondary" onClick={() => setCurrentStep('details')}>
              Back
            </Button>
            <Button variant="primary" onClick={handlePayment}>
              Process Payment
            </Button>
          </>
        )}
        
        {currentStep === 'thankyou' && (
          <Button variant="primary" onClick={handleClose} className="mx-auto">
            Close
          </Button>
        )}
      </Modal.Footer>
      

    </Modal>
  );
};

export default TicketBookingModal;