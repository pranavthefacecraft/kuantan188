import React, { useState } from 'react';
import { Modal, Button, Row, Col } from 'react-bootstrap';
import { Event } from '../../services/api';

interface ReservationModalProps {
  show: boolean;
  onHide: () => void;
  event: Event | null;
}

const ReservationModal: React.FC<ReservationModalProps> = ({ show, onHide, event }) => {
  const [quantity, setQuantity] = useState(2);
  const [showDatePicker, setShowDatePicker] = useState(false);
  
  const handleQuantityChange = (change: number) => {
    const newQuantity = quantity + change;
    if (newQuantity >= 1) {
      setQuantity(newQuantity);
    }
  };

  const calculateTotal = () => {
    if (!event) return 1000;
    const price = typeof event.price === 'string' 
      ? parseFloat(event.price.replace(/[^0-9.]/g, '')) || 500
      : event.price || 500;
    return price * quantity;
  };

  const handleAddToCart = () => {
    console.log('Adding to cart:', {
      event: event?.title,
      quantity,
      total: calculateTotal()
    });
    
    alert(`Added to cart!\n${event?.title}\nQuantity: ${quantity}\nTotal: ₹${calculateTotal()}`);
    onHide();
  };

  if (!event) return null;

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
          borderRadius: '20px',
          overflow: 'hidden',
          minHeight: '500px'
        }}>
          {/* Header */}
          <div className="d-flex justify-content-between align-items-center p-4 border-bottom">
            <div className="d-flex align-items-center">
              <h5 className="mb-0 fw-bold">Tickets</h5>
              <span className="badge text-white ms-3 px-3 py-2 rounded-pill fs-6" 
                    style={{ backgroundColor: '#00c851' }}>
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
                
                {/* Quantity Selector */}
                <div className="mb-4">
                  <div className="d-flex justify-content-between align-items-center">
                    <div>
                      <div className="fw-bold mb-1" style={{ fontSize: '1rem' }}>Adult</div>
                      <small className="text-muted" style={{ fontSize: '0.8rem' }}>from $500</small>
                    </div>
                    <div className="d-flex align-items-center">
                      <Button 
                        variant="outline-success"
                        className="rounded-circle d-flex align-items-center justify-content-center border-2"
                        style={{ 
                          width: '36px', 
                          height: '36px',
                          borderColor: '#00c851',
                          color: '#00c851',
                          fontSize: '18px'
                        }}
                        onClick={() => handleQuantityChange(-1)}
                        disabled={quantity <= 1}
                      >
                        −
                      </Button>
                      <span className="mx-4 fw-bold" style={{ fontSize: '1.2rem' }}>
                        {quantity}
                      </span>
                      <Button 
                        variant="outline-success"
                        className="rounded-circle d-flex align-items-center justify-content-center border-2"
                        style={{ 
                          width: '36px', 
                          height: '36px',
                          borderColor: '#00c851',
                          color: '#00c851',
                          fontSize: '16px'
                        }}
                        onClick={() => handleQuantityChange(1)}
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
                    Today and tomorrow are fully booked. Check future dates for availability!
                  </span>
                </div>
                <Button 
                  variant="outline-success" 
                  className="border-2 rounded-3 px-4 py-2 fw-medium"
                  style={{ 
                    borderColor: '#00c851',
                    color: '#00c851',
                    fontSize: '0.9rem'
                  }}
                  onClick={() => setShowDatePicker(!showDatePicker)}
                >
                  <i className="fas fa-calendar-alt me-2" style={{ color: '#00c851' }}></i>
                  Other Dates
                </Button>
              </div>


            </Col>
          </Row>

          {/* Right Aligned Add to Cart Button */}
          <div className="px-4 pb-4 d-flex justify-content-end" style={{ backgroundColor: '#00c851' }}>
            <Button 
              className="fw-bold py-2 px-4 rounded-3 border-0"
              onClick={handleAddToCart}
              style={{
                backgroundColor: '#00c851',
                fontSize: '0.9rem'
              }}
            >
              Add to cart
              <i className="fas fa-arrow-right ms-2"></i>
            </Button>
          </div>
        </div>
      </Modal.Body>
    </Modal>
  );
};

export default ReservationModal;