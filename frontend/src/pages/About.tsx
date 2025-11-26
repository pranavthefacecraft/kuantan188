import React from 'react';
import { Container, Row, Col, Card, Button } from 'react-bootstrap';

const About: React.FC = () => {
  const teamMembers = [
    {
      name: "Ahmad Rahman",
      role: "Founder & CEO",
      image: "https://via.placeholder.com/200x200/6c63ff/ffffff?text=AR",
      description: "Passionate about bringing amazing events to Kuantan community."
    },
    {
      name: "Siti Nuraini",
      role: "Event Manager",
      image: "https://via.placeholder.com/200x200/28a745/ffffff?text=SN",
      description: "Expert in event planning and customer experience management."
    },
    {
      name: "David Lim",
      role: "Technology Director",
      image: "https://via.placeholder.com/200x200/dc3545/ffffff?text=DL",
      description: "Leading our digital platform and innovative ticketing solutions."
    }
  ];

  return (
    <Container className="py-5">
      {/* Hero Section */}
      <Row className="mb-5">
        <Col lg={8} className="mx-auto text-center">
          <h1 className="fw-bold mb-4">About Kuantan188</h1>
          <p className="lead text-muted">
            We are passionate about connecting people with amazing experiences through 
            seamless event ticketing and exceptional customer service.
          </p>
        </Col>
      </Row>

      {/* Our Story Section */}
      <Row className="mb-5 align-items-center">
        <Col lg={6} className="mb-4">
          <div className="bg-light rounded-4 p-4 h-100 d-flex align-items-center justify-content-center">
            <div className="text-center">
              <div className="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                   style={{ width: '100px', height: '100px' }}>
                <span className="text-white fs-1">🎪</span>
              </div>
              <h4>Our Story</h4>
            </div>
          </div>
        </Col>
        <Col lg={6}>
          <h2 className="fw-bold mb-4">How We Started</h2>
          <p className="text-muted mb-3">
            Founded in 2020, Kuantan188 began with a simple mission: to make event discovery 
            and ticket booking as easy and enjoyable as the events themselves. We noticed that 
            people in Kuantan were missing out on amazing events due to complicated booking 
            processes and lack of centralized information.
          </p>
          <p className="text-muted mb-3">
            Starting with just a handful of local events, we've grown to become Kuantan's 
            premier event ticketing platform, serving thousands of happy customers and 
            partnering with hundreds of event organizers.
          </p>
          <p className="text-muted">
            Today, we continue to innovate and expand our services while staying true to 
            our core values of simplicity, reliability, and community connection.
          </p>
        </Col>
      </Row>

      {/* Mission & Values Section */}
      <Row className="mb-5 py-5 bg-light rounded-4">
        <Col>
          <h2 className="text-center fw-bold mb-5">Our Mission & Values</h2>
          <Row>
            <Col lg={4} className="mb-4 text-center">
              <div className="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                   style={{ width: '80px', height: '80px' }}>
                <span className="text-white fs-2">🎯</span>
              </div>
              <h5 className="fw-bold mb-3">Our Mission</h5>
              <p className="text-muted">
                To connect people with unforgettable experiences by providing the most 
                user-friendly and reliable event ticketing platform in Malaysia.
              </p>
            </Col>
            <Col lg={4} className="mb-4 text-center">
              <div className="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                   style={{ width: '80px', height: '80px' }}>
                <span className="text-white fs-2">❤️</span>
              </div>
              <h5 className="fw-bold mb-3">Community First</h5>
              <p className="text-muted">
                We believe in supporting local events, artists, and organizers to build 
                a vibrant cultural and entertainment scene in Kuantan.
              </p>
            </Col>
            <Col lg={4} className="mb-4 text-center">
              <div className="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                   style={{ width: '80px', height: '80px' }}>
                <span className="text-white fs-2">⚡</span>
              </div>
              <h5 className="fw-bold mb-3">Innovation</h5>
              <p className="text-muted">
                We continuously improve our platform with the latest technology to 
                provide the best possible user experience.
              </p>
            </Col>
          </Row>
        </Col>
      </Row>

      {/* Team Section */}
      <Row className="mb-5">
        <Col>
          <h2 className="text-center fw-bold mb-5">Meet Our Team</h2>
          <Row>
            {teamMembers.map((member, index) => (
              <Col lg={4} key={index} className="mb-4">
                <Card className="border-0 shadow-sm text-center h-100">
                  <Card.Body className="p-4">
                    <img 
                      src={member.image} 
                      alt={member.name}
                      className="rounded-circle mb-3"
                      width="120"
                      height="120"
                      style={{ objectFit: 'cover' }}
                    />
                    <Card.Title className="fw-bold mb-1">{member.name}</Card.Title>
                    <Card.Subtitle className="text-primary mb-3">{member.role}</Card.Subtitle>
                    <Card.Text className="text-muted">
                      {member.description}
                    </Card.Text>
                  </Card.Body>
                </Card>
              </Col>
            ))}
          </Row>
        </Col>
      </Row>

      {/* Stats Section */}
      <Row className="mb-5 text-center">
        <Col>
          <h2 className="fw-bold mb-5">Our Impact</h2>
          <Row>
            <Col lg={3} md={6} className="mb-4">
              <div className="p-4">
                <h2 className="display-4 fw-bold text-primary mb-2">10K+</h2>
                <p className="text-muted mb-0">Happy Customers</p>
              </div>
            </Col>
            <Col lg={3} md={6} className="mb-4">
              <div className="p-4">
                <h2 className="display-4 fw-bold text-success mb-2">500+</h2>
                <p className="text-muted mb-0">Events Hosted</p>
              </div>
            </Col>
            <Col lg={3} md={6} className="mb-4">
              <div className="p-4">
                <h2 className="display-4 fw-bold text-warning mb-2">50+</h2>
                <p className="text-muted mb-0">Partner Venues</p>
              </div>
            </Col>
            <Col lg={3} md={6} className="mb-4">
              <div className="p-4">
                <h2 className="display-4 fw-bold text-danger mb-2">99%</h2>
                <p className="text-muted mb-0">Satisfaction Rate</p>
              </div>
            </Col>
          </Row>
        </Col>
      </Row>

      {/* CTA Section */}
      <Row className="text-center">
        <Col lg={8} className="mx-auto">
          <h2 className="fw-bold mb-3">Join Our Community</h2>
          <p className="lead text-muted mb-4">
            Ready to discover amazing events? Start exploring and book your next adventure today!
          </p>
          <div className="d-flex justify-content-center gap-3 flex-wrap">
            <Button variant="primary" size="lg">
              Browse Events
            </Button>
            <Button variant="outline-primary" size="lg">
              Contact Us
            </Button>
          </div>
        </Col>
      </Row>
    </Container>
  );
};

export default About;