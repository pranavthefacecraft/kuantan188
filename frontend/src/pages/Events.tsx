import React, { useState } from 'react';
import { Container, Row, Col, Card, Button, Badge, Form, InputGroup } from 'react-bootstrap';
import { Event } from '../services/api';
import ReservationModal from '../components/modals/ReservationModal';

const Events: React.FC = () => {
  const [showModal, setShowModal] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState<Event | null>(null);

  const handleReserveNow = (event: any) => {
    // Convert the hardcoded event to Event interface format
    const eventData: Event = {
      id: event.id,
      title: event.title,
      description: event.description,
      location: event.location,
      event_date: new Date().toISOString(),
      event_date_formatted: event.date,
      event_time_formatted: event.time,
      image_url: event.image,
      price: event.price,
      price_display: event.price,
      category: event.category,
      is_booking_open: true,
      slug: event.title.toLowerCase().replace(/\s+/g, '-')
    };
    setSelectedEvent(eventData);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setSelectedEvent(null);
  };

  const events = [
    {
      id: 1,
      title: "Music Festival 2024",
      date: "March 15, 2024",
      time: "7:00 PM",
      location: "Kuantan Stadium",
      price: "From RM89",
      image: "https://via.placeholder.com/400x250/6c63ff/ffffff?text=Music+Festival",
      category: "Music",
      description: "Join us for an unforgettable night of music with local and international artists."
    },
    {
      id: 2,
      title: "Comedy Night Live",
      date: "March 22, 2024",
      time: "8:30 PM",
      location: "Cultural Center",
      price: "From RM55",
      image: "https://via.placeholder.com/400x250/28a745/ffffff?text=Comedy+Night",
      category: "Comedy",
      description: "Laugh until your sides hurt with Malaysia's top comedians performing live."
    },
    {
      id: 3,
      title: "Art Exhibition Opening",
      date: "April 5, 2024",
      time: "6:00 PM",
      location: "Gallery Kuantan",
      price: "From RM25",
      image: "https://via.placeholder.com/400x250/dc3545/ffffff?text=Art+Exhibition",
      category: "Art",
      description: "Explore contemporary Malaysian art from emerging and established artists."
    },
    {
      id: 4,
      title: "Food Festival Weekend",
      date: "April 12-14, 2024",
      time: "10:00 AM",
      location: "Esplanade Kuantan",
      price: "From RM15",
      image: "https://via.placeholder.com/400x250/ffc107/ffffff?text=Food+Festival",
      category: "Food",
      description: "Taste amazing local and international cuisine from top food vendors."
    },
    {
      id: 5,
      title: "Tech Conference 2024",
      date: "April 20, 2024",
      time: "9:00 AM",
      location: "Convention Center",
      price: "From RM120",
      image: "https://via.placeholder.com/400x250/17a2b8/ffffff?text=Tech+Conference",
      category: "Technology",
      description: "Learn from industry experts about the latest trends in technology and innovation."
    },
    {
      id: 6,
      title: "Cultural Dance Performance",
      date: "May 3, 2024",
      time: "7:30 PM",
      location: "Grand Theater",
      price: "From RM65",
      image: "https://via.placeholder.com/400x250/6f42c1/ffffff?text=Cultural+Dance",
      category: "Culture",
      description: "Experience the beauty of traditional Malaysian dance and cultural performances."
    }
  ];

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
      <Row>
        {events.map((event) => (
          <Col lg={4} md={6} key={event.id} className="mb-4">
            <Card className="h-100 border-0 shadow-sm hover-lift">
              <div className="position-relative">
                <Card.Img 
                  variant="top" 
                  src={event.image} 
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
                <Card.Text className="text-muted small mb-2">
                  📅 {event.date} at {event.time}<br />
                  📍 {event.location}
                </Card.Text>
                <Card.Text className="text-muted mb-3 flex-grow-1">
                  {event.description}
                </Card.Text>
                <div className="d-flex justify-content-between align-items-center">
                  <span className="fw-bold text-primary fs-5">{event.price}</span>
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
        ))}
      </Row>

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
          <InputGroup className="mb-3" style={{ maxWidth: '400px', margin: '0 auto' }}>
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