import React from 'react';
import { Container, Row, Col } from 'react-bootstrap';

const Footer: React.FC = () => {
  return (
    <footer 
      className="text-light py-5 mt-auto"
      style={{
        backgroundImage: 'url(/bg-ticket-1.webp)',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        position: 'relative'
      }}
    >
      <div 
        className="position-absolute w-100 h-100" 
        style={{
          backgroundColor: 'rgba(0, 0, 0, 0.7)',
          top: 0,
          left: 0
        }}
      ></div>
      
      <Container className="position-relative" style={{ zIndex: 1 }}>
        <Row className="justify-content-center text-center">
          <Col lg={8}>
            {/* Social Media Icons */}
            <div className="mb-4">
              <a href="#" className="text-white me-4" style={{ fontSize: '24px' }}>
                <i className="fab fa-facebook-f"></i>
              </a>
              <a href="#" className="text-white me-4" style={{ fontSize: '24px' }}>
                <i className="fab fa-twitter"></i>
              </a>
              <a href="#" className="text-white me-4" style={{ fontSize: '24px' }}>
                <i className="fab fa-instagram"></i>
              </a>
              <a href="#" className="text-white" style={{ fontSize: '24px' }}>
                <i className="fab fa-linkedin-in"></i>
              </a>
            </div>
            
            {/* Kuantan188 Title */}
            <h2 className="footer-title mb-4">Kuantan188</h2>
            
            {/* Footer Menu */}
            <div className="footer-menu">
              <a href="/privacy-policy" className="text-white text-decoration-none me-3">Privacy & Policy</a>
              <span className="text-white me-3">|</span>
              <a href="/terms-conditions" className="text-white text-decoration-none me-3">Terms & Condition</a>
              <span className="text-white me-3">|</span>
              <a href="/contact" className="text-white text-decoration-none me-3">Contact</a>
              <span className="text-white me-3">|</span>
              <a href="https://tfcmockup.com" className="text-white text-decoration-none">Made by TFC</a>
            </div>
          </Col>
        </Row>
      </Container>
    </footer>
  );
};

export default Footer;