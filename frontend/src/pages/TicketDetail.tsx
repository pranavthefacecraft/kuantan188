import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Container, Row, Col, Card, Button, Badge, Spinner, Alert } from 'react-bootstrap';
import { eventsApi } from '../services/api';
import { format, addDays } from 'date-fns';
import TicketBookingModal from '../components/modals/TicketBookingModal';

interface TicketDetailProps {}

const TicketDetail: React.FC<TicketDetailProps> = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [ticket, setTicket] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<'malaysian' | 'non_malaysian'>('malaysian');
  
  // Booking state
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [selectedTime, setSelectedTime] = useState<string>('09:00');
  const [selectedCurrency, setSelectedCurrency] = useState('RM - MYR (Malaysia)');
  const [showCalendar, setShowCalendar] = useState(false);
  const [calendarMonth, setCalendarMonth] = useState(new Date());
  const [showBookingModal, setShowBookingModal] = useState(false);
  
  // Quantity state
  const [quantities, setQuantities] = useState({
    malaysian: {
      adult: 1,
      teenager: 0,
      university: 0,
      child: 0
    },
    non_malaysian: {
      adult: 0,
      teenager: 0,
      university: 0,
      child: 0
    }
  });

  // Pricing data
  const [pricingData, setPricingData] = useState<any>({
    malaysian: {
      adult_price: '100.00',
      child_price: '75.00',
      teenager_price: '85.00',
      university_price: '90.00',
      available: true
    },
    non_malaysian: {
      adult_price: '150.00',
      child_price: '125.00',
      teenager_price: '140.00',
      university_price: '145.00',
      available: true
    }
  });

  useEffect(() => {
    const loadTicketDetails = async () => {
      try {
        const ticketId = parseInt(id || '0');
        setLoading(true);
        
        const response = await eventsApi.getTicketById(ticketId);
        
        if (response.success && response.data) {
          const foundTicket = response.data;
          // Enhanced ticket data with booking modal structure
          const enhancedTicket = {
            ...foundTicket,
            // Use existing image URL or fallback to placeholder
            image_url: foundTicket.image_url || `https://picsum.photos/400/250?random=${foundTicket.id}`,
            // Use API title field directly
            display_title: foundTicket.ticket_name || foundTicket.name || foundTicket.event_title || 'General Admission',
            // Use API description or create based on ticket type
            description: foundTicket.description || 
                        (foundTicket.ticket_name && foundTicket.ticket_name.includes('Food') ? 
                        'Join us for an incredible culinary journey featuring local and international cuisines, food vendors, cooking demonstrations, and cultural performances.' :
                        'Enjoy this amazing experience with premium amenities and unforgettable memories. Your gateway to an extraordinary adventure.')
          };
          setTicket(enhancedTicket);
          
          // Set pricing based on API data
          const apiPricing = foundTicket.pricing;
          const baseFallback = parseFloat(foundTicket.base_price || '100');
          const pricing = {
            malaysian: {
              adult_price: apiPricing?.malaysian?.adult_price || baseFallback.toFixed(2),
              child_price: apiPricing?.malaysian?.child_price || (baseFallback * 0.75).toFixed(2),
              teenager_price: apiPricing?.malaysian?.teen_price || (baseFallback * 0.85).toFixed(2),
              university_price: apiPricing?.malaysian?.university_price || (baseFallback * 0.90).toFixed(2),
              available: apiPricing?.malaysian?.available ?? true
            },
            non_malaysian: {
              adult_price: apiPricing?.non_malaysian?.adult_price || (baseFallback * 1.5).toFixed(2),
              child_price: apiPricing?.non_malaysian?.child_price || (baseFallback * 1.25).toFixed(2),
              teenager_price: apiPricing?.non_malaysian?.teen_price || (baseFallback * 1.4).toFixed(2),
              university_price: apiPricing?.non_malaysian?.university_price || (baseFallback * 1.45).toFixed(2),
              available: apiPricing?.non_malaysian?.available ?? true
            }
          };
          setPricingData(pricing);
        } else {
          setError('Ticket not found');
        }
      } catch (err: any) {
        setError(err.response?.data?.message || 'Error loading ticket details');
        console.error('Error loading ticket:', err);
      } finally {
        setLoading(false);
      }
    };

    loadTicketDetails();
  }, [id]);

  // Helper functions for quantity management
  const handleQuantityChange = (category: 'adult' | 'teenager' | 'university' | 'child', change: number) => {
    const currentQuantities = quantities[activeTab];
    const newQuantity = Math.max(0, currentQuantities[category] + change);
    
    setQuantities(prev => ({
      ...prev,
      [activeTab]: {
        ...prev[activeTab],
        [category]: newQuantity
      }
    }));
  };

  const calculateTotal = () => {
    const currentQuantities = quantities[activeTab];
    const pricing = pricingData[activeTab];
    
    const total = 
      (currentQuantities.adult * parseFloat(pricing.adult_price)) +
      (currentQuantities.teenager * parseFloat(pricing.teenager_price)) +
      (currentQuantities.university * parseFloat(pricing.university_price)) +
      (currentQuantities.child * parseFloat(pricing.child_price));
    
    return total.toFixed(2);
  };

  const getTotalTickets = () => {
    const malayTotal = Object.values(quantities.malaysian).reduce((sum, qty) => sum + qty, 0);
    const nonMalayTotal = Object.values(quantities.non_malaysian).reduce((sum, qty) => sum + qty, 0);
    return malayTotal + nonMalayTotal;
  };

  // Time slots with best price indicators
  const timeSlots = [
    { time: '09:00', bestPrice: true },
    { time: '09:30', bestPrice: true },
    { time: '10:00', bestPrice: true },
    { time: '10:30', bestPrice: true },
    { time: '11:00', bestPrice: false },
    { time: '11:30', bestPrice: false },
    { time: '12:00', bestPrice: false },
    { time: '12:30', bestPrice: false },
    { time: '13:00', bestPrice: false },
    { time: '13:30', bestPrice: false },
    { time: '14:00', bestPrice: false },
    { time: '14:30', bestPrice: false },
  ];

  if (loading) {
    return (
      <Container className="py-5">
        <Row className="justify-content-center">
          <Col md={8} className="text-center">
            <Spinner animation="border" role="status" className="mb-3">
              <span className="visually-hidden">Loading...</span>
            </Spinner>
            <p>Loading ticket details...</p>
          </Col>
        </Row>
      </Container>
    );
  }

  if (error || !ticket) {
    return (
      <Container className="py-5">
        <Row className="justify-content-center">
          <Col md={8}>
            <Alert variant="danger" className="text-center">
              <Alert.Heading>Ticket Not Found</Alert.Heading>
              <p>{error || 'The requested ticket could not be found.'}</p>
              <Button variant="primary" onClick={() => navigate('/tickets')}>
                Back to My Tickets
              </Button>
            </Alert>
          </Col>
        </Row>
      </Container>
    );
  }

  return (
    <div>
      {/* Header Section */}
      <section className="bg-custom-dark text-white py-4">
        <Container>
          <Row className="align-items-center">
            <Col>
              <h1 className="h2 fw-bold mb-2">🎫 Ticket Details</h1>
            </Col>
          </Row>
        </Container>
      </section>

      {/* Main Booking Content */}
      <Container className="py-4">
        <Row>
          {/* Left Side - Event Image and Description */}
          <Col lg={5}>
            <Card className="border-0 shadow-sm h-100">
              <div 
                className="card-img-top"
                style={{
                  height: '250px',
                  backgroundImage: `url(${ticket.image_url})`,
                  backgroundSize: 'cover',
                  backgroundPosition: 'center',
                  backgroundColor: '#f8f9fa'
                }}
              />
              <Card.Body className="p-4">
                <h3 className="fw-bold mb-3">{ticket.display_title}</h3>
                <p className="text-muted mb-0">{ticket.description}</p>
              </Card.Body>
            </Card>
          </Col>

          {/* Right Side - Booking Form */}
          <Col lg={7}>
            <Card className="border-0 shadow-sm h-100">
              <Card.Body className="p-4">
                
                {/* Malaysian/Non-Malaysian Tabs */}
                <div className="mb-4">
                  <div className="d-flex border rounded overflow-hidden">
                    <Button
                      variant={activeTab === 'malaysian' ? 'primary' : 'outline-secondary'}
                      className="flex-fill border-0 rounded-0"
                      onClick={() => setActiveTab('malaysian')}
                    >
                      Malaysian
                    </Button>
                    <Button
                      variant={activeTab === 'non_malaysian' ? 'primary' : 'outline-secondary'}
                      className="flex-fill border-0 rounded-0"
                      onClick={() => setActiveTab('non_malaysian')}
                    >
                      Non-Malaysian
                    </Button>
                  </div>
                </div>

                {/* Quantity Selectors */}
                <div className="mb-4">
                  {/* Adult */}
                  <div className="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                      <div className="fw-bold">Adult</div>
                      <div className="text-muted small">from RM{pricingData[activeTab].adult_price}</div>
                    </div>
                    <div className="d-flex align-items-center">
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('adult', -1)}
                        disabled={quantities[activeTab].adult === 0}
                      >
                        -
                      </Button>
                      <span className="mx-3 fw-bold">{quantities[activeTab].adult}</span>
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('adult', 1)}
                      >
                        +
                      </Button>
                    </div>
                  </div>

                  {/* Teenagers */}
                  <div className="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                      <div className="fw-bold">Teenagers From 13</div>
                      <div className="text-muted small">from RM{pricingData[activeTab].teenager_price}</div>
                    </div>
                    <div className="d-flex align-items-center">
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('teenager', -1)}
                        disabled={quantities[activeTab].teenager === 0}
                      >
                        -
                      </Button>
                      <span className="mx-3 fw-bold">{quantities[activeTab].teenager}</span>
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('teenager', 1)}
                      >
                        +
                      </Button>
                    </div>
                  </div>

                  {/* University Students */}
                  <div className="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                      <div className="fw-bold">University Students</div>
                      <div className="text-muted small">from RM{pricingData[activeTab].university_price}</div>
                    </div>
                    <div className="d-flex align-items-center">
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('university', -1)}
                        disabled={quantities[activeTab].university === 0}
                      >
                        -
                      </Button>
                      <span className="mx-3 fw-bold">{quantities[activeTab].university}</span>
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('university', 1)}
                      >
                        +
                      </Button>
                    </div>
                  </div>

                  {/* Children */}
                  <div className="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                      <div className="fw-bold">Children Below 13</div>
                      <div className="text-muted small">from RM{pricingData[activeTab].child_price}</div>
                    </div>
                    <div className="d-flex align-items-center">
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('child', -1)}
                        disabled={quantities[activeTab].child === 0}
                      >
                        -
                      </Button>
                      <span className="mx-3 fw-bold">{quantities[activeTab].child}</span>
                      <Button
                        variant="outline-secondary"
                        size="sm"
                        className="rounded-circle"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => handleQuantityChange('child', 1)}
                      >
                        +
                      </Button>
                    </div>
                  </div>
                </div>

                {/* Total */}
                <div className="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-4">
                  <div>
                    <div className="fw-bold">Total for {getTotalTickets()} ticket(s)</div>
                  </div>
                  <div className="text-end">
                    <div className="h4 fw-bold text-success mb-0">RM{calculateTotal()}</div>
                    <div className="small text-muted">{getTotalTickets()} ticket(s)</div>
                  </div>
                </div>
              </Card.Body>
            </Card>
          </Col>
        </Row>

        {/* Date, Currency, and Time Selection */}
        <Row className="mt-4">
          <Col>
            <Card className="border-0 shadow-sm">
              <Card.Body className="p-4">
                <Row>
                  {/* Select Date */}
                  <Col md={4}>
                    <h6 className="fw-bold mb-3">Select Date</h6>
                    <div className="d-flex gap-2">
                      <Button
                        variant={format(selectedDate, 'yyyy-MM-dd') === format(new Date(), 'yyyy-MM-dd') ? 'success' : 'outline-secondary'}
                        className="d-flex flex-column align-items-center p-3"
                        onClick={() => { setSelectedDate(new Date()); setShowCalendar(false); }}
                      >
                        <div className="h5 mb-1">{format(new Date(), 'd')}</div>
                        <small>Today</small>
                      </Button>
                      <Button
                        variant={format(selectedDate, 'yyyy-MM-dd') === format(addDays(new Date(), 1), 'yyyy-MM-dd') ? 'success' : 'outline-secondary'}
                        className="d-flex flex-column align-items-center p-3"
                        onClick={() => { setSelectedDate(addDays(new Date(), 1)); setShowCalendar(false); }}
                      >
                        <div className="h5 mb-1">{format(addDays(new Date(), 1), 'd')}</div>
                        <small>Tomorrow</small>
                      </Button>
                      {(() => {
                        const today = new Date();
                        const tomorrow = addDays(new Date(), 1);
                        const isCustomDate = format(selectedDate, 'yyyy-MM-dd') !== format(today, 'yyyy-MM-dd') &&
                                           format(selectedDate, 'yyyy-MM-dd') !== format(tomorrow, 'yyyy-MM-dd');
                        return (
                          <Button
                            variant={isCustomDate ? 'success' : 'outline-secondary'}
                            className="d-flex flex-column align-items-center p-3"
                            onClick={() => setShowCalendar(!showCalendar)}
                          >
                            {isCustomDate ? (
                              <>
                                <div className="h5 mb-1">{format(selectedDate, 'd')}</div>
                                <small>{format(selectedDate, 'MMM')}</small>
                              </>
                            ) : (
                              <>
                                <div className="h5 mb-1">📅</div>
                                <small>Other Dates</small>
                              </>
                            )}
                          </Button>
                        );
                      })()}
                    </div>

                    {/* Inline Calendar */}
                    {showCalendar && (
                      <div className="inline-calendar-container mt-3">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                          <Button variant="link" className="p-0 text-muted" onClick={() => setCalendarMonth(new Date(calendarMonth.getFullYear(), calendarMonth.getMonth() - 1, 1))}>
                            <span>&lt;</span>
                          </Button>
                          <h6 className="mb-0 fw-bold">{format(calendarMonth, 'MMMM yyyy')}</h6>
                          <Button variant="link" className="p-0 text-muted" onClick={() => setCalendarMonth(new Date(calendarMonth.getFullYear(), calendarMonth.getMonth() + 1, 1))}>
                            <span>&gt;</span>
                          </Button>
                        </div>

                        <div className="calendar-grid">
                          <div className="calendar-weekdays mb-2">
                            {['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'].map(day => (
                              <div key={day} className="weekday text-muted text-center small">{day}</div>
                            ))}
                          </div>

                          <div className="calendar-days">
                            {(() => {
                              const year = calendarMonth.getFullYear();
                              const month = calendarMonth.getMonth();
                              const daysInMonth = new Date(year, month + 1, 0).getDate();
                              const firstDayOfWeek = (new Date(year, month, 1).getDay() + 6) % 7; // Monday=0
                              const today = new Date();
                              const tomorrowDate = addDays(today, 1);

                              const cells: React.ReactNode[] = [];
                              // Empty cells for days before the 1st
                              for (let i = 0; i < firstDayOfWeek; i++) {
                                cells.push(<div key={`empty-${i}`} className="calendar-day empty"></div>);
                              }
                              for (let day = 1; day <= daysInMonth; day++) {
                                const cellDate = new Date(year, month, day);
                                const isToday = format(cellDate, 'yyyy-MM-dd') === format(today, 'yyyy-MM-dd');
                                const isTomorrow = format(cellDate, 'yyyy-MM-dd') === format(tomorrowDate, 'yyyy-MM-dd');
                                const isSelected = format(selectedDate, 'yyyy-MM-dd') === format(cellDate, 'yyyy-MM-dd');

                                cells.push(
                                  <div
                                    key={day}
                                    className={`calendar-day ${isToday || isTomorrow ? 'highlight' : ''} ${isSelected ? 'selected' : ''}`}
                                    onClick={() => {
                                      setSelectedDate(cellDate);
                                      setShowCalendar(false);
                                    }}
                                  >
                                    <div className="day-number">{day}</div>
                                  </div>
                                );
                              }
                              return cells;
                            })()}
                          </div>
                        </div>
                      </div>
                    )}
                  </Col>

                  {/* Select Currency */}
                  <Col md={4}>
                    <h6 className="fw-bold mb-3">Select Currency</h6>
                    <div className="dropdown">
                      <select 
                        className="form-select"
                        value={selectedCurrency}
                        onChange={(e) => setSelectedCurrency(e.target.value)}
                      >
                        <option value="RM - MYR (Malaysia)">RM - MYR (Malaysia)</option>
                        <option value="USD - Dollar (USA)">USD - Dollar (USA)</option>
                        <option value="EUR - Euro (Europe)">EUR - Euro (Europe)</option>
                      </select>
                    </div>
                  </Col>

                  {/* Select Time */}
                  <Col md={4}>
                    <h6 className="fw-bold mb-3">Select time</h6>
                    <div className="row g-2" style={{ maxHeight: '200px', overflowY: 'auto' }}>
                      {timeSlots.map((slot) => (
                        <div className="col-6" key={slot.time}>
                          <Button
                            variant={selectedTime === slot.time ? 'success' : 'outline-secondary'}
                            className="w-100 position-relative"
                            size="sm"
                            onClick={() => setSelectedTime(slot.time)}
                          >
                            {slot.time}
                            {slot.bestPrice && (
                              <Badge 
                                bg="success" 
                                className="position-absolute top-0 start-100 translate-middle"
                                style={{ fontSize: '10px' }}
                              >
                                Best price
                              </Badge>
                            )}
                          </Button>
                        </div>
                      ))}
                    </div>
                  </Col>
                </Row>

                {/* Book Now Button */}
                <div className="text-center mt-4">
                  <Button 
                    variant="primary" 
                    size="lg" 
                    className="px-5"
                    disabled={getTotalTickets() === 0}
                    onClick={() => setShowBookingModal(true)}
                  >
                    Book Now - RM{calculateTotal()}
                  </Button>
                </div>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>

      {/* Booking Modal */}
      <TicketBookingModal
        show={showBookingModal}
        onHide={() => setShowBookingModal(false)}
        ticket={ticket}
        initialStep="details"
        initialMalaysianQuantities={quantities.malaysian}
        initialNonMalaysianQuantities={quantities.non_malaysian}
        initialDate={selectedDate}
        initialTime={selectedTime}
      />
    </div>
  );
};

export default TicketDetail;