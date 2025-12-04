import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Container, Row, Col, Card, Button, Badge, Spinner, Alert } from 'react-bootstrap';
import { Calendar, MapPin, Clock, Users, DollarSign, ArrowLeft } from 'lucide-react';
import { eventsApi, Event } from '../services/api';
import ReservationModal from '../components/modals/ReservationModal';

interface EventDetailData extends Event {
  name?: string;
  event_time?: string;
  capacity?: number;
  current_bookings?: number;
  status?: string;
  created_at?: string;
}

const EventDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [event, setEvent] = useState<EventDetailData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showReservationModal, setShowReservationModal] = useState(false);

  useEffect(() => {
    const fetchEvent = async () => {
      if (!id) {
        setError('Event ID not found');
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        const response = await eventsApi.getEventById(parseInt(id));
        
        if (response.success) {
          setEvent(response.data);
        } else {
          setError('Event not found');
        }
      } catch (err) {
        console.error('Error fetching event:', err);
        setError('Failed to load event details');
      } finally {
        setLoading(false);
      }
    };

    fetchEvent();
  }, [id]);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatTime = (timeString?: string) => {
    if (!timeString) return 'Time TBD';
    const time = new Date(`2000-01-01T${timeString}`);
    return time.toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  };

  const getAvailableSpots = () => {
    if (!event || !event.capacity || !event.current_bookings) return 0;
    return (event.capacity || 0) - (event.current_bookings || 0);
  };

  const getStatusBadge = (status: string) => {
    switch (status.toLowerCase()) {
      case 'active':
        return <Badge bg="success">Active</Badge>;
      case 'full':
        return <Badge bg="danger">Fully Booked</Badge>;
      case 'cancelled':
        return <Badge bg="secondary">Cancelled</Badge>;
      default:
        return <Badge bg="warning">Unknown</Badge>;
    }
  };

  const handleBookNow = () => {
    if (event && getAvailableSpots() > 0 && (event.status && event.status.toLowerCase() === 'active')) {
      setShowReservationModal(true);
    }
  };

  const isBookingAvailable = () => {
    return event && 
           (event.is_booking_open || (event.status && event.status.toLowerCase() === 'active')) && 
           getAvailableSpots() > 0 &&
           new Date(event.event_date) > new Date();
  };

  if (loading) {
    return (
      <Container className="py-5">
        <div className="text-center">
          <Spinner animation="border" role="status" variant="primary" />
          <p className="mt-3">Loading event details...</p>
        </div>
      </Container>
    );
  }

  if (error || !event) {
    return (
      <Container className="py-5">
        <Alert variant="danger">
          <Alert.Heading>Event Not Found</Alert.Heading>
          <p>{error || 'The requested event could not be found.'}</p>
          <hr />
          <Button variant="outline-primary" onClick={() => navigate('/')}>
            <ArrowLeft size={20} className="me-2" />
            Back to Home
          </Button>
        </Alert>
      </Container>
    );
  }

  return (
    <div className="event-detail-page">
      <Container className="py-5">
        <Row className="g-4">
          {/* Event Image */}
          <Col lg={6}>
            <Card className="h-100 border-0 shadow-sm">
              <div 
                className="event-card-background h-100 d-flex flex-column"
                style={{
                  height: '400px',
                  backgroundImage: event.image_url 
                    ? `url(${event.image_url})` 
                    : `linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)`,
                  backgroundColor: '#1A0007',
                  backgroundSize: 'cover',
                  backgroundPosition: 'center',
                  borderRadius: '0.375rem'
                }}
                onError={(e) => {
                  // Fallback if image fails to load
                  const target = e.target as HTMLElement;
                  target.style.backgroundImage = 'linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)';
                }}
              >
                <div className="event-card-overlay"></div>
                {!event.image_url && (
                  <div className="d-flex align-items-center justify-content-center h-100">
                    <div className="text-center text-white">
                      <Calendar size={64} className="mb-3" />
                      <h4>Event Image</h4>
                    </div>
                  </div>
                )}
              </div>
            </Card>
          </Col>

          {/* Event Details */}
          <Col lg={6}>
            <Card className="h-100 border-0 shadow-sm">
              <Card.Body className="p-4">
                <div className="d-flex justify-content-between align-items-start mb-3">
                  <h1 className="h2 mb-0">{event.name || event.title}</h1>
                  {getStatusBadge(event.status || 'active')}
                </div>

                <p className="text-muted mb-4">{event.description}</p>

                <div className="event-info mb-4">
                  <Row className="g-3">
                    <Col sm={6}>
                      <div className="d-flex align-items-center">
                        <Calendar className="text-primary me-3" size={20} />
                        <div>
                          <small className="text-muted d-block">Date</small>
                          <strong>{formatDate(event.event_date)}</strong>
                        </div>
                      </div>
                    </Col>
                    <Col sm={6}>
                      <div className="d-flex align-items-center">
                        <Clock className="text-primary me-3" size={20} />
                        <div>
                          <small className="text-muted d-block">Time</small>
                          <strong>{event.event_time_formatted || formatTime(event.event_time)}</strong>
                        </div>
                      </div>
                    </Col>
                    <Col sm={6}>
                      <div className="d-flex align-items-center">
                        <MapPin className="text-primary me-3" size={20} />
                        <div>
                          <small className="text-muted d-block">Location</small>
                          <strong>{event.location}</strong>
                        </div>
                      </div>
                    </Col>
                    <Col sm={6}>
                      <div className="d-flex align-items-center">
                        <Users className="text-primary me-3" size={20} />
                        <div>
                          <small className="text-muted d-block">Availability</small>
                          <strong>{getAvailableSpots()} of {event.capacity || 100} spots</strong>
                        </div>
                      </div>
                    </Col>
                    {event.price && (
                      <Col sm={6}>
                        <div className="d-flex align-items-center">
                          <DollarSign className="text-primary me-3" size={20} />
                          <div>
                            <small className="text-muted d-block">Price</small>
                            <strong>{event.price_display || `$${event.price}` || 'Contact for pricing'}</strong>
                          </div>
                        </div>
                      </Col>
                    )}
                  </Row>
                </div>

                {/* Booking Section */}
                <div className="booking-section">
                  {isBookingAvailable() ? (
                    <div>
                      <div className="bg-light p-3 rounded mb-3">
                        <div className="d-flex justify-content-between align-items-center">
                          <span className="fw-bold">Available Spots:</span>
                          <Badge bg={getAvailableSpots() > 10 ? 'success' : getAvailableSpots() > 5 ? 'warning' : 'danger'}>
                            {getAvailableSpots()} left
                          </Badge>
                        </div>
                      </div>
                      <Button 
                        variant="primary" 
                        size="lg" 
                        className="w-100"
                        onClick={handleBookNow}
                      >
                        Book Now
                      </Button>
                    </div>
                  ) : (
                    <div className="text-center">
                      {event.status && event.status.toLowerCase() !== 'active' && (
                        <Alert variant="warning">This event is not currently available for booking.</Alert>
                      )}
                      {getAvailableSpots() <= 0 && event.status && event.status.toLowerCase() === 'active' && (
                        <Alert variant="danger">This event is fully booked.</Alert>
                      )}
                      {new Date(event.event_date) <= new Date() && (
                        <Alert variant="info">This event has already passed.</Alert>
                      )}
                      <Button variant="secondary" size="lg" disabled className="w-100">
                        Booking Not Available
                      </Button>
                    </div>
                  )}
                </div>

                <hr className="my-4" />
                
                {/* Additional Info */}
                <div className="additional-info">
                  <small className="text-muted">
                    {event.created_at && `Event created on ${new Date(event.created_at).toLocaleDateString()}`}
                  </small>
                </div>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>

      {/* Reservation Modal */}
      {showReservationModal && (
        <ReservationModal
          show={showReservationModal}
          onHide={() => setShowReservationModal(false)}
          event={event}
        />
      )}
    </div>
  );
};

export default EventDetail;