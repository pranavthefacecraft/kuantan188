import React from 'react';
import { Navbar, Nav, Container, Button } from 'react-bootstrap';
import { Link, useLocation } from 'react-router-dom';

const Header: React.FC = () => {
  const location = useLocation();

  return (
    <Navbar expand="lg" className="position-absolute w-100" style={{ backgroundColor: 'transparent', zIndex: 1000, top: 0 }}>
      <Container>
        <Navbar.Brand as={Link} to="/" className="d-flex align-items-center">
          <img 
            src="/Secondary_White.svg" 
            alt="Kuantan188 Logo" 
            width="80"
            height="23"
            className="me-2"
          />
        </Navbar.Brand>

        <Navbar.Toggle aria-controls="basic-navbar-nav" />
        <Navbar.Collapse id="basic-navbar-nav">
          <Nav className="mx-auto erstoria-nav">
            <Nav.Link 
              as={Link} 
              to="/" 
              className={location.pathname === '/' ? 'active fw-semibold' : ''}
            >
              Home
            </Nav.Link>
            <Nav.Link 
              as={Link} 
              to="/sky-deck" 
              className={location.pathname === '/sky-deck' ? 'active fw-semibold' : ''}
            >
              Sky Deck
            </Nav.Link>
            <Nav.Link 
              as={Link} 
              to="/observation-deck" 
              className={location.pathname === '/observation-deck' ? 'active fw-semibold' : ''}
            >
              Observation Deck
            </Nav.Link>
            <Nav.Link 
              as={Link} 
              to="/sky-walk" 
              className={location.pathname === '/sky-walk' ? 'active fw-semibold' : ''}
            >
              Sky Walk
            </Nav.Link>
            
          </Nav>
          
          <div className="d-flex">
            <Button className="shop-button">
              Shop
            </Button>
          </div>
        </Navbar.Collapse>
      </Container>
    </Navbar>
  );
};

export default Header;