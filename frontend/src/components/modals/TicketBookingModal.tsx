import React, { useState } from 'react';
import { Modal, Button, Row, Col, Form } from 'react-bootstrap';

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
      size="lg"
      backdrop="static"
      keyboard={false}
      className="ticket-booking-modal"
    >
      <Modal.Header closeButton className="modal-header-custom">
        <Modal.Title className="modal-title-custom">
          🎫 {ticketName}
        </Modal.Title>
      </Modal.Header>
      
      <Modal.Body className="modal-body-custom">
        {currentStep === 'selection' && (
          <div className="ticket-selection-step">
            {/* Ticket Info */}
            <div className="ticket-info mb-4">
              {ticket.image_url && (
                <div className="ticket-image mb-3">
                  <img 
                    src={ticket.image_url} 
                    alt={ticketName}
                    className="img-fluid rounded"
                    style={{ maxHeight: '200px', width: '100%', objectFit: 'cover' }}
                  />
                </div>
              )}
              <h4 className="ticket-title">{ticketName}</h4>
              {ticket.description && (
                <p className="ticket-description text-muted">{ticket.description}</p>
              )}
            </div>

            {/* Country Selection */}
            {ticket.countries && ticket.countries.length > 1 && (
              <div className="country-selection mb-4">
                <label className="form-label fw-bold">Select Your Country/Region:</label>
                <Form.Select
                  value={selectedCountry?.id || ''}
                  onChange={(e) => {
                    const country = ticket.countries?.find(c => c.id === parseInt(e.target.value));
                    setSelectedCountry(country);
                  }}
                  className="form-select-custom"
                >
                  {ticket.countries.map((country) => (
                    <option key={country.id} value={country.id}>
                      {country.name} ({country.currency_symbol})
                    </option>
                  ))}
                </Form.Select>
              </div>
            )}

            {/* Quantity Selection */}
            {selectedCountry && (
              <div className="quantity-selection">
                <h5 className="mb-3">Select Tickets:</h5>
                
                {/* Adult Tickets */}
                <div className="ticket-row mb-3 p-3 border rounded">
                  <Row className="align-items-center">
                    <Col md={6}>
                      <div className="ticket-type">
                        <h6 className="mb-1">Adult</h6>
                        <p className="text-muted mb-0">
                          {selectedCountry.currency_symbol}{parseFloat(selectedCountry.adult_price).toFixed(2)}
                        </p>
                      </div>
                    </Col>
                    <Col md={6}>
                      <div className="quantity-controls d-flex align-items-center justify-content-end">
                        <Button 
                          variant="outline-secondary" 
                          size="sm"
                          onClick={() => handleQuantityChange('adult', -1)}
                          disabled={adultQuantity === 0}
                        >
                          -
                        </Button>
                        <span className="quantity-display mx-3">{adultQuantity}</span>
                        <Button 
                          variant="outline-secondary" 
                          size="sm"
                          onClick={() => handleQuantityChange('adult', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </Col>
                  </Row>
                </div>

                {/* Child Tickets */}
                <div className="ticket-row mb-3 p-3 border rounded">
                  <Row className="align-items-center">
                    <Col md={6}>
                      <div className="ticket-type">
                        <h6 className="mb-1">Child</h6>
                        <p className="text-muted mb-0">
                          {selectedCountry.currency_symbol}{parseFloat(selectedCountry.child_price).toFixed(2)}
                        </p>
                      </div>
                    </Col>
                    <Col md={6}>
                      <div className="quantity-controls d-flex align-items-center justify-content-end">
                        <Button 
                          variant="outline-secondary" 
                          size="sm"
                          onClick={() => handleQuantityChange('child', -1)}
                          disabled={childQuantity === 0}
                        >
                          -
                        </Button>
                        <span className="quantity-display mx-3">{childQuantity}</span>
                        <Button 
                          variant="outline-secondary" 
                          size="sm"
                          onClick={() => handleQuantityChange('child', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </Col>
                  </Row>
                </div>

                {/* Total */}
                <div className="total-section mt-4 p-3 bg-light rounded">
                  <Row>
                    <Col>
                      <strong>Total ({getTotalQuantity()} ticket{getTotalQuantity() !== 1 ? 's' : ''}):</strong>
                    </Col>
                    <Col className="text-end">
                      <strong className="text-primary">
                        {selectedCountry.currency_symbol}{calculateTotal().toFixed(2)}
                      </strong>
                    </Col>
                  </Row>
                </div>
              </div>
            )}
          </div>
        )}

        {currentStep === 'details' && (
          <div className="contact-details-step">
            <h4 className="mb-4">Contact Details</h4>
            
            <Row>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>First Name *</Form.Label>
                  <Form.Control
                    type="text"
                    value={contactForm.firstName}
                    onChange={(e) => setContactForm({...contactForm, firstName: e.target.value})}
                    required
                  />
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Last Name *</Form.Label>
                  <Form.Control
                    type="text"
                    value={contactForm.lastName}
                    onChange={(e) => setContactForm({...contactForm, lastName: e.target.value})}
                    required
                  />
                </Form.Group>
              </Col>
            </Row>

            <Row>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Email *</Form.Label>
                  <Form.Control
                    type="email"
                    value={contactForm.email}
                    onChange={(e) => setContactForm({...contactForm, email: e.target.value})}
                    required
                  />
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Mobile Phone *</Form.Label>
                  <Form.Control
                    type="tel"
                    value={contactForm.mobilePhone}
                    onChange={(e) => setContactForm({...contactForm, mobilePhone: e.target.value})}
                    required
                  />
                </Form.Group>
              </Col>
            </Row>

            <Row>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Country</Form.Label>
                  <Form.Control
                    type="text"
                    value={contactForm.country}
                    onChange={(e) => setContactForm({...contactForm, country: e.target.value})}
                  />
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Postal Code</Form.Label>
                  <Form.Control
                    type="text"
                    value={contactForm.postalCode}
                    onChange={(e) => setContactForm({...contactForm, postalCode: e.target.value})}
                  />
                </Form.Group>
              </Col>
            </Row>

            <Form.Group className="mb-3">
              <Form.Check
                type="checkbox"
                label="I would like to receive updates about events and special offers"
                checked={contactForm.receiveUpdates}
                onChange={(e) => setContactForm({...contactForm, receiveUpdates: e.target.checked})}
              />
            </Form.Group>

            {/* Order Summary */}
            <div className="order-summary mt-4 p-3 bg-light rounded">
              <h5>Order Summary</h5>
              <p><strong>{ticketName}</strong></p>
              {adultQuantity > 0 && (
                <p>Adult × {adultQuantity}: {selectedCountry?.currency_symbol}{(parseFloat(selectedCountry?.adult_price || '0') * adultQuantity).toFixed(2)}</p>
              )}
              {childQuantity > 0 && (
                <p>Child × {childQuantity}: {selectedCountry?.currency_symbol}{(parseFloat(selectedCountry?.child_price || '0') * childQuantity).toFixed(2)}</p>
              )}
              <hr />
              <p className="mb-0"><strong>Total: {selectedCountry?.currency_symbol}{calculateTotal().toFixed(2)}</strong></p>
            </div>
          </div>
        )}

        {currentStep === 'payment' && (
          <div className="payment-step text-center">
            <h4 className="mb-4">Payment Processing</h4>
            <div className="payment-simulation">
              <div className="spinner-border text-primary mb-3" role="status">
                <span className="visually-hidden">Processing...</span>
              </div>
              <p>Processing your payment securely...</p>
              <p className="text-muted">This is a demo - no actual payment will be processed</p>
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
              <p><strong>Quantity:</strong> {getTotalQuantity()} ticket{getTotalQuantity() !== 1 ? 's' : ''}</p>
              <p><strong>Total Paid:</strong> {selectedCountry?.currency_symbol}{calculateTotal().toFixed(2)}</p>
            </div>
          </div>
        )}
      </Modal.Body>
      
      <Modal.Footer className="modal-footer-custom">
        {currentStep === 'selection' && (
          <>
            <Button variant="secondary" onClick={handleClose}>
              Cancel
            </Button>
            <Button 
              variant="primary" 
              onClick={handleContinueToDetails}
              disabled={getTotalQuantity() === 0}
            >
              Continue to Details
            </Button>
          </>
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