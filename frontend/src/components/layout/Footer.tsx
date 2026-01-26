import React from 'react';
import { Container, Row, Col } from 'react-bootstrap';

const Footer: React.FC = () => {
  return (
    <footer className="footer-background text-light py-5 mt-auto">
      
      <Container className="footer-content">
        <Row className="justify-content-center text-center">
          <Col lg={8}>
            {/* Social Media Icons */}
            <div className="mb-4" style={{ marginTop: '200px' }}>
              <a href="https://www.facebook.com/kuantan188" target="_blank" rel="noopener noreferrer" className="footer-social-icon me-4">
                <i className="fab fa-facebook-f"></i>
              </a>
              <a href="https://www.instagram.com/kuantan188" target="_blank" rel="noopener noreferrer" className="footer-social-icon me-4">
                <i className="fab fa-instagram"></i>
              </a>
              <a href="https://www.tiktok.com/@kuantan188" target="_blank" rel="noopener noreferrer" className="footer-social-icon">
                <i className="fab fa-tiktok"></i>
              </a>
            </div>
            
            {/* Kuantan188 Title */}
            <h2 className="footer-title footer-title-responsive mb-4">Kuantan188</h2>
            
            {/* Footer Menu */}
            <div className="footer-menu" style={{ marginTop: '80px' }}>
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