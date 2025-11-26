import React, { useState } from 'react';
import { Navbar, Nav, Container, Row, Col, Dropdown, Badge } from 'react-bootstrap';
import { useAuth } from '../../contexts/AuthContext';
import { useNavigate, useLocation, Outlet } from 'react-router-dom';

interface DashboardLayoutProps {
  children?: React.ReactNode;
}

const menuItems = [
  { text: 'Dashboard', path: '/admin' },
  { text: 'Events', path: '/admin/events' },
  { text: 'Tickets', path: '/admin/tickets' },
  { text: 'Bookings', path: '/admin/bookings' },
  { text: 'Countries', path: '/admin/countries' },
];

const DashboardLayout: React.FC<DashboardLayoutProps> = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { user, logout } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const currentPageTitle = menuItems.find(item => item.path === location.pathname)?.text || 'Admin Panel';

  return (
    <div className="d-flex">
      {/* Sidebar */}
      <div className={`sidebar col-auto ${sidebarOpen ? 'show' : ''}`} style={{ width: '280px' }}>
        <div className="d-flex flex-column h-100">
          {/* Logo Section */}
          <div className="p-4 text-center border-bottom">
            <h3 className="text-white mb-0 fw-bold">🎫 Kuantan188</h3>
            <small className="text-white-50">Admin Panel</small>
          </div>

          {/* Navigation */}
          <Nav className="flex-column p-3 flex-grow-1">
            {menuItems.map((item) => (
              <Nav.Link
                key={item.path}
                onClick={() => navigate(item.path)}
                className={`nav-link ${location.pathname === item.path ? 'active' : ''}`}
                style={{ cursor: 'pointer' }}
              >
                {item.text}
              </Nav.Link>
            ))}
          </Nav>

          {/* User Info */}
          <div className="p-3 border-top">
            <Dropdown>
              <Dropdown.Toggle 
                variant="link" 
                className="text-white text-decoration-none d-flex align-items-center w-100"
                style={{ border: 'none' }}
              >
                <div className="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center me-2" 
                     style={{ width: '32px', height: '32px', fontSize: '14px' }}>
                  {user?.name?.charAt(0)?.toUpperCase() || 'A'}
                </div>
                <div className="text-start">
                  <div className="fw-semibold">{user?.name || 'Admin'}</div>
                  <small className="text-white-50">{user?.email}</small>
                </div>
              </Dropdown.Toggle>

              <Dropdown.Menu>
                <Dropdown.Item href="#profile">Profile</Dropdown.Item>
                <Dropdown.Item href="#settings">Settings</Dropdown.Item>
                <Dropdown.Divider />
                <Dropdown.Item onClick={handleLogout}>Logout</Dropdown.Item>
              </Dropdown.Menu>
            </Dropdown>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-grow-1" style={{ marginLeft: sidebarOpen ? '0' : '0' }}>
        {/* Top Navigation */}
        <Navbar bg="white" className="border-bottom px-4 py-3">
          <div className="d-flex align-items-center w-100">
            <button
              className="btn btn-link d-md-none me-3"
              onClick={() => setSidebarOpen(!sidebarOpen)}
              style={{ border: 'none' }}
            >
              ☰
            </button>
            
            <h5 className="mb-0 flex-grow-1">{currentPageTitle}</h5>
            
            <div className="d-flex align-items-center">
              <button className="btn btn-link position-relative me-3" style={{ border: 'none' }}>
                🔔
                <Badge bg="danger" className="position-absolute top-0 start-100 translate-middle badge-sm">
                  3
                </Badge>
              </button>
            </div>
          </div>
        </Navbar>

        {/* Page Content */}
        <div className="p-4" style={{ backgroundColor: 'var(--light-bg)', minHeight: 'calc(100vh - 73px)' }}>
          <Outlet />
        </div>
      </div>
    </div>
  );
};

export default DashboardLayout;