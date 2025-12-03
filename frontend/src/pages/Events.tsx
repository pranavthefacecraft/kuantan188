import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Card, Button, Badge, Form, InputGroup, Spinner, Alert } from 'react-bootstrap';
import { Event, eventsApi } from '../services/api';
import ReservationModal from '../components/modals/ReservationModal';

const Events: React.FC = () => {
  const [showModal, setShowModal] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState<Event | null>(null);
  const [events, setEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    const fetchEvents = async () => {
      try {
        setLoading(true);
        const response = await eventsApi.getEvents();
        if (response.success) {
          setEvents(response.data);
        } else {
          setError('Failed to load events');
        }
      } catch (err) {
        console.error('Error fetching events:', err);
        setError('Failed to load events. Please try again later.');
      } finally {
        setLoading(false);
      }
    };

    fetchEvents();
  }, []);

  const handleReserveNow = (event: Event) => {
    setSelectedEvent(event);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setSelectedEvent(null);
  };

  // Filter events based on search term
  const filteredEvents = events.filter(event =>
    event.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    event.location?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    event.category?.toLowerCase().includes(searchTerm.toLowerCase())
  );



  const categories = ["All", "Music", "Comedy", "Art", "Food", "Technology", "Culture"];

  return (
    <Container className="py-5">
      {/* Header Section */}
      <Row className="mb-5">
        <Col>
          <h1 className="fw-bold text-center mb-3">All Events</h1>
          <p className="text-center text-muted lead">
            Discover amazing events happening in Kuantan. Find your next adventure!
          </p>
        </Col>
      </Row>

      {/* Search and Filter Section */}
      <Row className="mb-5">
        <Col lg={8} className="mb-3">
          <InputGroup>
            <Form.Control
              type="search"
              placeholder="Search events by name, location, or category..."
              className="border-end-0"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
            <Button variant="primary">
              🔍 Search
            </Button>
          </InputGroup>
        </Col>
        <Col lg={4}>
          <Form.Select>
            <option value="">All Categories</option>
            {categories.slice(1).map(category => (
              <option key={category} value={category}>{category}</option>
            ))}
          </Form.Select>
        </Col>
      </Row>

      {/* Category Badges */}
      <Row className="mb-4">
        <Col>
          <div className="d-flex gap-2 flex-wrap">
            {categories.map(category => (
              <Badge 
                key={category}
                bg={category === "All" ? "primary" : "outline-primary"}
                className="px-3 py-2 cursor-pointer"
                style={{ cursor: 'pointer' }}
              >
                {category}
              </Badge>
            ))}
          </div>
        </Col>
      </Row>

      {/* Events Grid */}
      {loading ? (
        <div className="text-center py-5">
          <Spinner animation="border" variant="primary" />
          <p className="mt-3">Loading events...</p>
        </div>
      ) : error ? (
        <Alert variant="danger" className="text-center">
          <Alert.Heading>Oops!</Alert.Heading>
          <p>{error}</p>
        </Alert>
      ) : (
        <Row>
          {filteredEvents.length === 0 ? (
            <Col className="text-center py-5">
              <p className="text-muted">No events found matching your criteria.</p>
            </Col>
          ) : (
            filteredEvents.map((event) => (
              <Col lg={4} md={6} key={event.id} className="mb-4">
                <Card className="h-100 border-0 shadow-sm hover-lift">
                  <div className="position-relative">
                    <Card.Img 
                      variant="top" 
                      src={event.image_url} 
                      alt={event.title}
                      className="event-card-img"
                      onError={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.src = `https://picsum.photos/400/250?random=${event.id}`;
                      }}
                    />
                    <Badge 
                      bg="primary" 
                      className="position-absolute top-0 end-0 m-3"
                    >
                      {event.category}
                    </Badge>
                  </div>
                  <Card.Body className="d-flex flex-column">
                    <Card.Title className="fw-bold mb-3">{event.title}</Card.Title>
                    
                    {/* Event Details Section */}
                    <div className="event-details mb-3">
                      <div className="d-flex align-items-center mb-2">
                        <i className="fas fa-calendar-alt text-primary me-2"></i>
                        <span className="small">
                          <strong>{event.event_date_formatted}</strong> at {event.event_time_formatted}
                        </span>
                      </div>
                      <div className="d-flex align-items-center mb-2">
                        <i className="fas fa-map-marker-alt text-primary me-2"></i>
                        <span className="small">{event.location}</span>
                      </div>
                      <div className="d-flex align-items-center mb-2">
                        <i className="fas fa-tag text-primary me-2"></i>
                        <span className="small">{event.category}</span>
                      </div>
                      {event.is_booking_open && (
                        <div className="d-flex align-items-center mb-2">
                          <i className="fas fa-ticket-alt text-success me-2"></i>
                          <span className="small text-success">
                            <strong>Booking Open</strong>
                          </span>
                        </div>
                      )}
                    </div>
                    
                    <Card.Text className="text-muted mb-3 flex-grow-1">
                      {event.description}
                    </Card.Text>
                    <div className="d-flex justify-content-between align-items-center">
                      <span className="fw-bold text-primary fs-5">{event.price_display}</span>
                      <Button 
                        variant="primary" 
                        size="sm"
                        onClick={() => handleReserveNow(event)}
                      >
                        Reserve Now
                      </Button>
                    </div>
                  </Card.Body>
                </Card>
              </Col>
            ))
          )}
        </Row>
      )}

      {/* Load More Button */}
      <Row className="mt-5">
        <Col className="text-center">
          <Button variant="outline-primary" size="lg">
            Load More Events
          </Button>
        </Col>
      </Row>

      {/* Newsletter Section */}
      <Row className="mt-5 py-5 bg-light rounded-4">
        <Col lg={8} className="mx-auto text-center">
          <h3 className="fw-bold mb-3">Never Miss an Event</h3>
          <p className="text-muted mb-4">
            Subscribe to our newsletter to get notified about new events and exclusive deals.
          </p>
          <InputGroup className="event-search-container mb-3">
            <Form.Control
              type="email"
              placeholder="Enter your email"
            />
            <Button variant="primary">
              Subscribe
            </Button>
          </InputGroup>
        </Col>
      </Row>

      {/* Reservation Modal */}
      <ReservationModal 
        show={showModal}
        onHide={handleCloseModal}
        event={selectedEvent}
      />
    </Container>
  );
};

export default Events;