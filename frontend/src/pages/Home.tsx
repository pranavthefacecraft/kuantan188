import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Card, Button, Badge, Spinner, Alert } from 'react-bootstrap';
import { eventsApi, Event } from '../services/api';
import ReservationModal from '../components/modals/ReservationModal';

const Home: React.FC = () => {
  const [featuredEvents, setFeaturedEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState<Event | null>(null);


  useEffect(() => {
    const fetchFeaturedEvents = async () => {
      try {
        setLoading(true);
        const response = await eventsApi.getFeaturedEvents();
        if (response.success) {
          setFeaturedEvents(response.data);
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

    fetchFeaturedEvents();
  }, []);

  const handleReserveNow = (event: Event) => {
    setSelectedEvent(event);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setSelectedEvent(null);
  };

  return (
    <div>
      {/* Hero Section */}
      <section className="hero-section py-5 mb-5">
        <Container>
          <Row className="align-items-center min-vh-50">
            <Col lg={6}>
              <h1 className="display-4 fw-bold mb-4">
                Discover Amazing Events in <span className="gradient-text">Kuantan</span>
              </h1>
              <p className="lead mb-4 text-muted">
                From concerts and festivals to workshops and exhibitions, find the perfect event 
                that matches your interests. Book your tickets easily and securely.
              </p>
              <div className="d-flex gap-3 flex-wrap">
                <Button variant="primary" size="lg">
                  Browse Events
                </Button>
                <Button variant="outline-primary" size="lg">
                  Learn More
                </Button>
              </div>
            </Col>
            <Col lg={6} className="text-center">
              <div className="hero-image-placeholder bg-light rounded-4 p-5">
                <div className="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                     style={{ width: '200px', height: '200px' }}>
                  <span className="text-white" style={{ fontSize: '4rem' }}>🎪</span>
                </div>
              </div>
            </Col>
          </Row>
        </Container>
      </section>

      {/* Featured Events Section */}
      <section className="py-5">
        <Container>
          <Row className="mb-4">
            <Col>
              <h2 className="text-center fw-bold mb-4">Featured Events</h2>
              <p className="text-center text-muted">
                Don't miss these amazing upcoming events in Kuantan
              </p>
            </Col>
          </Row>
                <Row>
            {loading ? (
              <Col className="text-center">
                <Spinner animation="border" role="status" variant="primary">
                  <span className="visually-hidden">Loading events...</span>
                </Spinner>
                <p className="mt-2 text-muted">Loading amazing events...</p>
              </Col>
            ) : error ? (
            <Col>
              <Alert variant="warning" className="text-center">
                <Alert.Heading>Oops! Something went wrong</Alert.Heading>
                <p>{error}</p>
                <Button 
                  variant="outline-warning" 
                  onClick={() => window.location.reload()}
                >
                  Try Again
                </Button>
              </Alert>
            </Col>
          ) : (
            <>
              {featuredEvents.map((event) => (
                <Col lg={4} md={6} key={event.id} className="mb-4">
                  <Card className="h-100 border-0 shadow-sm hover-lift">
                    <div className="position-relative">
                      <Card.Img 
                        variant="top" 
                        src={event.image_url} 
                        alt={event.title}
                        style={{ height: '200px', objectFit: 'cover' }}
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
                      <Card.Title className="fw-bold mb-2">{event.title}</Card.Title>
                      <Card.Text className="text-muted mb-3 flex-grow-1">
                        📅 {event.event_date_formatted} at {event.event_time_formatted}<br />
                        📍 {event.location}
                      </Card.Text>
                      <div className="d-flex justify-content-between align-items-center">
                        <span className="fw-bold text-primary">{event.price_display}</span>
                        <Button 
                          variant="outline-primary" 
                          size="sm"
                          onClick={() => handleReserveNow(event)}
                        >
                          Reserve Now
                        </Button>
                      </div>
                    </Card.Body>
                  </Card>
                </Col>
              ))}
              {featuredEvents.length === 0 && !loading && (
                <Col className="text-center">
                  <div className="py-5">
                    <h5 className="text-muted">No events available at the moment</h5>
                    <p className="text-muted">Check back soon for exciting upcoming events!</p>
                  </div>
                </Col>
              )}
            </>
          )}
          </Row>
        </Container>
      </section>

      {/* Reservation Modal */}
      <ReservationModal 
        show={showModal}
        onHide={handleCloseModal}
        event={selectedEvent}
      />
    </div>
  );
};

export default Home;