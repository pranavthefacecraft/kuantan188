import React from 'react';
import { Container, Row, Col } from 'react-bootstrap';

const Footer: React.FC = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-dark text-light py-5 mt-auto">
      <Container>
        <Row>
          <Col lg={4} md={6} className="mb-4">
            <h5 className="fw-bold mb-3">🎫 Kuantan188</h5>
            <p className="text-light-emphasis">
              Your premier destination for amazing events and entertainment experiences.
              Book your tickets today and create unforgettable memories.
            </p>
          </Col>
          
          <Col lg={2} md={6} className="mb-4">
            <h6 className="fw-semibold mb-3">Quick Links</h6>
            <ul className="list-unstyled">
              <li className="mb-2">
                <a href="/" className="text-light-emphasis text-decoration-none hover-primary">
                  Home
                </a>
              </li>
              <li className="mb-2">
                <a href="/events" className="text-light-emphasis text-decoration-none hover-primary">
                  Events
                </a>
              </li>
              <li className="mb-2">
                <a href="/about" className="text-light-emphasis text-decoration-none hover-primary">
                  About Us
                </a>
              </li>
              <li className="mb-2">
                <a href="/contact" className="text-light-emphasis text-decoration-none hover-primary">
                  Contact
                </a>
              </li>
            </ul>
          </Col>
          
          <Col lg={3} md={6} className="mb-4">
            <h6 className="fw-semibold mb-3">Services</h6>
            <ul className="list-unstyled">
              <li className="mb-2">
                <span className="text-light-emphasis">Event Ticketing</span>
              </li>
              <li className="mb-2">
                <span className="text-light-emphasis">Group Bookings</span>
              </li>
              <li className="mb-2">
                <span className="text-light-emphasis">VIP Packages</span>
              </li>
              <li className="mb-2">
                <span className="text-light-emphasis">Corporate Events</span>
              </li>
            </ul>
          </Col>
          
          <Col lg={3} md={6} className="mb-4">
            <h6 className="fw-semibold mb-3">Contact Info</h6>
            <ul className="list-unstyled">
              <li className="mb-2 text-light-emphasis">
                📍 Kuantan, Pahang, Malaysia
              </li>
              <li className="mb-2 text-light-emphasis">
                📞 +60 12-345-6789
              </li>
              <li className="mb-2 text-light-emphasis">
                ✉️ info@kuantan188.com
              </li>
              <li className="mb-2">
                <div className="d-flex gap-3 mt-3">
                  <a href="#" className="text-light-emphasis hover-primary">
                    <i className="fab fa-facebook-f"></i> Facebook
                  </a>
                  <a href="#" className="text-light-emphasis hover-primary">
                    <i className="fab fa-instagram"></i> Instagram
                  </a>
                  <a href="#" className="text-light-emphasis hover-primary">
                    <i className="fab fa-twitter"></i> Twitter
                  </a>
                </div>
              </li>
            </ul>
          </Col>
        </Row>
        
        <hr className="my-4 border-secondary" />
        
        <Row className="align-items-center">
          <Col md={6}>
            <p className="mb-0 text-light-emphasis">
              &copy; {currentYear} Kuantan188. All rights reserved.
            </p>
          </Col>
          <Col md={6} className="text-md-end">
            <div className="d-flex justify-content-md-end gap-3 mt-2 mt-md-0">
              <a href="#" className="text-light-emphasis text-decoration-none hover-primary">
                Privacy Policy
              </a>
              <a href="#" className="text-light-emphasis text-decoration-none hover-primary">
                Terms of Service
              </a>
              <a href="#" className="text-light-emphasis text-decoration-none hover-primary">
                Support
              </a>
            </div>
          </Col>
        </Row>
      </Container>
    </footer>
  );
};

export default Footer;