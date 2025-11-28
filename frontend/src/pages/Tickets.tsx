import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Card, Button, Badge, Spinner } from 'react-bootstrap';
import { testTickets } from '../data/testTickets';

const Tickets: React.FC = () => {
  const [tickets, setTickets] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Load tickets from test data (for debugging)
    const loadTickets = () => {
      console.log('Loading test tickets:', testTickets);
      setTickets(testTickets);
      setLoading(false);
    };

    // Uncomment this to use API instead of test data
    // const loadTickets = async () => {
    //   try {
    //     const response = await eventsApi.getTickets();
    //     if (response.success) {
    //       setTickets(response.data);
    //     } else {
    //       setError('Failed to load tickets');
    //     }
    //   } catch (err) {
    //     setError('Error loading tickets');
    //     console.error('Error loading tickets:', err);
    //   } finally {
    //     setLoading(false);
    //   }
    // };

    loadTickets();
  }, []);

  if (loading) {
    return (
      <Container className="py-5">
        <Row className="justify-content-center">
          <Col md={8} className="text-center">
            <Spinner animation="border" role="status" className="mb-3">
              <span className="visually-hidden">Loading...</span>
            </Spinner>
            <p>Loading your tickets...</p>
          </Col>
        </Row>
      </Container>
    );
  }



  return (
    <div>
      {/* Hero Section */}
      <section className="bg-primary text-white py-5 mb-5">
        <Container>
          <Row className="text-center">
            <Col>
              <h1 className="display-4 fw-bold mb-3">🎫 My Tickets</h1>
              <p className="lead mb-0">
                Manage and view all your event tickets in one place
              </p>
            </Col>
          </Row>
        </Container>
      </section>

      {/* Tickets Section */}
      <section className="py-5">
        <Container>
          <Row className="mb-4">
            <Col>
              <h2 className="fw-bold mb-3">Your Event Tickets</h2>
              <p className="text-muted">
                {tickets.length > 0 ? `You have ${tickets.length} ticket(s)` : 'No tickets found'}
              </p>
            </Col>
          </Row>

          <Row>
            {tickets.length === 0 ? (
              <Col className="text-center">
                <div className="py-5">
                  <div className="mb-4">
                    <span className="display-1">🎫</span>
                  </div>
                  <h3 className="text-muted mb-3">No tickets yet</h3>
                  <p className="text-muted mb-4">
                    Book your first event ticket to see them here
                  </p>
                  <Button variant="primary" size="lg" href="/">
                    Browse Events
                  </Button>
                </div>
              </Col>
            ) : (
              tickets.map((ticket, index) => (
                <Col lg={4} md={6} key={index} className="mb-4">
                  <Card className="h-100 border-0 shadow-sm hover-shadow">
                    <Card.Header className="bg-gradient" style={{background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'}}>
                      <div className="d-flex justify-content-between align-items-center text-white">
                        <span className="fw-bold">🎫 Ticket #{ticket.id}</span>
                        <Badge bg={ticket.status === 'confirmed' ? 'success' : ticket.status === 'pending' ? 'warning' : 'secondary'}>
                          {ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}
                        </Badge>
                      </div>
                    </Card.Header>
                    <Card.Body className="p-4">
                      <Card.Title className="fw-bold mb-3 text-primary">{ticket.event_title}</Card.Title>
                      <Card.Text className="text-muted mb-4">
                        <div className="mb-2">
                          <i className="fas fa-calendar-alt me-2"></i>
                          <strong>Event Date:</strong> {new Date(ticket.event_date).toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                          })}
                        </div>
                        <div className="mb-2">
                          <i className="fas fa-ticket-alt me-2"></i>
                          <strong>Quantity:</strong> {ticket.quantity} ticket(s)
                        </div>
                        <div className="mb-2">
                          <i className="fas fa-calendar-check me-2"></i>
                          <strong>Booked:</strong> {new Date(ticket.booking_date).toLocaleDateString()}
                        </div>
                      </Card.Text>
                      <div className="d-flex justify-content-between align-items-center">
                        <span className="fw-bold text-success fs-5">
                          Total: RM{ticket.total_price}
                        </span>
                        <div>
                          <Button variant="outline-primary" size="sm" className="me-2">
                            View Details
                          </Button>
                          <Button variant="outline-secondary" size="sm">
                            Download
                          </Button>
                        </div>
                      </div>
                    </Card.Body>
                  </Card>
                </Col>
              ))
            )}
          </Row>

          {/* Summary Section */}
          {tickets.length > 0 && (
            <Row className="mt-5">
              <Col>
                <Card className="bg-light border-0">
                  <Card.Body className="p-4">
                    <Row className="text-center">
                      <Col md={4}>
                        <div className="mb-3">
                          <span className="display-6">🎫</span>
                        </div>
                        <h5 className="fw-bold">{tickets.length}</h5>
                        <p className="text-muted mb-0">Total Tickets</p>
                      </Col>
                      <Col md={4}>
                        <div className="mb-3">
                          <span className="display-6">✅</span>
                        </div>
                        <h5 className="fw-bold">
                          {tickets.filter(t => t.status === 'confirmed').length}
                        </h5>
                        <p className="text-muted mb-0">Confirmed</p>
                      </Col>
                      <Col md={4}>
                        <div className="mb-3">
                          <span className="display-6">💰</span>
                        </div>
                        <h5 className="fw-bold">
                          RM{tickets.reduce((sum, ticket) => sum + ticket.total_price, 0).toFixed(2)}
                        </h5>
                        <p className="text-muted mb-0">Total Spent</p>
                      </Col>
                    </Row>
                  </Card.Body>
                </Card>
              </Col>
            </Row>
          )}
        </Container>
      </section>
    </div>
  );
};

export default Tickets;