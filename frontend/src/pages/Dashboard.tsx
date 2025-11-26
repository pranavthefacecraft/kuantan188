import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card, Badge, Spinner } from 'react-bootstrap';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
} from 'recharts';
import apiClient from '../services/api';
import { Event, Booking } from '../types';

// Sample data for charts
const bookingTrends = [
  { name: 'Jan', bookings: 65, revenue: 15400 },
  { name: 'Feb', bookings: 78, revenue: 18200 },
  { name: 'Mar', bookings: 90, revenue: 22100 },
  { name: 'Apr', bookings: 81, revenue: 19800 },
  { name: 'May', bookings: 95, revenue: 24500 },
  { name: 'Jun', bookings: 112, revenue: 28900 },
];

const eventTypeData = [
  { name: 'Concerts', value: 45, color: '#6366f1' },
  { name: 'Festivals', value: 30, color: '#8b5cf6' },
  { name: 'Sports', value: 15, color: '#10b981' },
  { name: 'Others', value: 10, color: '#f59e0b' },
];

interface DashboardStats {
  totalBookings: number;
  totalRevenue: number;
  activeEvents: number;
  pendingBookings: number;
  totalCustomers: number;
  totalTickets: number;
}

interface StatsCardProps {
  title: string;
  value: string | number;
  change?: number;
  changeLabel?: string;
  icon: string;
  color: string;
}

const StatsCard: React.FC<StatsCardProps> = ({
  title,
  value,
  change,
  changeLabel,
  icon,
  color,
}) => {
  return (
    <Card className="stats-card h-100">
      <Card.Body>
        <div className="d-flex justify-content-between align-items-start mb-3">
          <div>
            <p className="text-muted text-uppercase small fw-semibold mb-1" style={{ fontSize: '0.75rem', letterSpacing: '0.5px' }}>
              {title}
            </p>
            <h3 className="mb-0 fw-bold" style={{ color: `var(--${color}-color)` }}>
              {value}
            </h3>
          </div>
          <div className={`stats-icon ${color}`}>
            {icon}
          </div>
        </div>
        
        {change !== undefined && (
          <div className="d-flex align-items-center">
            <span className={`fw-semibold me-1 ${change >= 0 ? 'text-success' : 'text-danger'}`}>
              {change >= 0 ? '↗' : '↘'} {Math.abs(change)}%
            </span>
            {changeLabel && (
              <small className="text-muted">{changeLabel}</small>
            )}
          </div>
        )}
      </Card.Body>
    </Card>
  );
};

const Dashboard: React.FC = () => {
  const [stats, setStats] = useState<DashboardStats>({
    totalBookings: 0,
    totalRevenue: 0,
    activeEvents: 0,
    pendingBookings: 0,
    totalCustomers: 0,
    totalTickets: 0,
  });
  const [events, setEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setLoading(true);
        
        // Fetch events
        const eventsResponse = await apiClient.get('/events');
        const eventsData = eventsResponse.data;
        setEvents(eventsData);

        // Mock dashboard stats (you can implement actual API endpoints)
        setStats({
          totalBookings: 1247,
          totalRevenue: 85429.50,
          activeEvents: eventsData.length,
          pendingBookings: 23,
          totalCustomers: 892,
          totalTickets: 2450,
        });
        
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        // Set default stats if API fails
        setStats({
          totalBookings: 1247,
          totalRevenue: 85429.50,
          activeEvents: 8,
          pendingBookings: 23,
          totalCustomers: 892,
          totalTickets: 2450,
        });
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  const getStatusBadge = (status: string) => {
    const statusConfig = {
      pending: { variant: 'warning' as const, label: 'Pending' },
      confirmed: { variant: 'success' as const, label: 'Confirmed' },
      cancelled: { variant: 'danger' as const, label: 'Cancelled' },
    };
    
    const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.pending;
    
    return (
      <Badge bg={config.variant}>
        {config.label}
      </Badge>
    );
  };

  if (loading) {
    return (
      <div className="loading-spinner">
        <Spinner animation="border" role="status" variant="primary">
          <span className="visually-hidden">Loading...</span>
        </Spinner>
      </div>
    );
  }

  return (
    <Container fluid>
      {/* Welcome Section */}
      <div className="mb-4">
        <h1 className="display-5 fw-bold gradient-text mb-2">
          Welcome back! 👋
        </h1>
        <p className="text-muted">
          Here's what's happening with your ticket sales today.
        </p>
      </div>

      {/* Stats Cards */}
      <Row className="g-4 mb-4">
        <Col xs={12} sm={6} md={4} lg={2}>
          <StatsCard
            title="Total Bookings"
            value={stats.totalBookings.toLocaleString()}
            change={12.5}
            changeLabel="from last month"
            icon="📊"
            color="primary"
          />
        </Col>
        <Col xs={12} sm={6} md={4} lg={2}>
          <StatsCard
            title="Revenue"
            value={formatCurrency(stats.totalRevenue)}
            change={8.2}
            changeLabel="from last month"
            icon="💰"
            color="success"
          />
        </Col>
        <Col xs={12} sm={6} md={4} lg={2}>
          <StatsCard
            title="Active Events"
            value={stats.activeEvents}
            change={-2.1}
            changeLabel="from last month"
            icon="🎉"
            color="info"
          />
        </Col>
        <Col xs={12} sm={6} md={4} lg={2}>
          <StatsCard
            title="Pending Bookings"
            value={stats.pendingBookings}
            icon="⏳"
            color="warning"
          />
        </Col>
        <Col xs={12} sm={6} md={4} lg={2}>
          <StatsCard
            title="Total Customers"
            value={stats.totalCustomers.toLocaleString()}
            change={15.3}
            changeLabel="from last month"
            icon="👥"
            color="secondary"
          />
        </Col>
        <Col xs={12} sm={6} md={4} lg={2}>
          <StatsCard
            title="Total Tickets"
            value={stats.totalTickets.toLocaleString()}
            change={5.7}
            changeLabel="from last month"
            icon="🎫"
            color="error"
          />
        </Col>
      </Row>

      {/* Charts Section */}
      <Row className="g-4 mb-4">
        {/* Booking Trends */}
        <Col xs={12} lg={8}>
          <Card style={{ height: '400px' }}>
            <Card.Header>
              <h6 className="mb-0 fw-semibold">Booking Trends</h6>
              <small className="text-muted">Monthly booking and revenue overview</small>
            </Card.Header>
            <Card.Body>
              <ResponsiveContainer width="100%" height={280}>
                <LineChart data={bookingTrends}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                  <XAxis 
                    dataKey="name" 
                    stroke="#64748b"
                    fontSize={12}
                  />
                  <YAxis stroke="#64748b" fontSize={12} />
                  <Tooltip
                    contentStyle={{
                      background: 'rgba(255, 255, 255, 0.95)',
                      backdropFilter: 'blur(20px)',
                      border: 'none',
                      borderRadius: '12px',
                      boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)',
                    }}
                  />
                  <Line
                    type="monotone"
                    dataKey="bookings"
                    stroke="#6366f1"
                    strokeWidth={3}
                    dot={{ fill: '#6366f1', strokeWidth: 2, r: 6 }}
                    activeDot={{ r: 8, stroke: '#6366f1', strokeWidth: 2 }}
                  />
                </LineChart>
              </ResponsiveContainer>
            </Card.Body>
          </Card>
        </Col>

        {/* Event Distribution */}
        <Col xs={12} lg={4}>
          <Card style={{ height: '400px' }}>
            <Card.Header>
              <h6 className="mb-0 fw-semibold">Event Types</h6>
              <small className="text-muted">Distribution by category</small>
            </Card.Header>
            <Card.Body>
              <ResponsiveContainer width="100%" height={280}>
                <PieChart>
                  <Pie
                    data={eventTypeData}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={100}
                    paddingAngle={5}
                    dataKey="value"
                  >
                    {eventTypeData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip
                    contentStyle={{
                      background: 'rgba(255, 255, 255, 0.95)',
                      backdropFilter: 'blur(20px)',
                      border: 'none',
                      borderRadius: '12px',
                      boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)',
                    }}
                  />
                </PieChart>
              </ResponsiveContainer>
            </Card.Body>
          </Card>
        </Col>
      </Row>

      {/* Recent Activity */}
      <Row className="g-4">
        {/* Active Events */}
        <Col xs={12} lg={6}>
          <Card>
            <Card.Header>
              <h6 className="mb-0 fw-semibold">Active Events</h6>
              <small className="text-muted">Currently available for booking</small>
            </Card.Header>
            <Card.Body style={{ maxHeight: '300px', overflowY: 'auto' }}>
              {events.length > 0 ? (
                events.map((event) => (
                  <div
                    key={event.id}
                    className="p-3 mb-2 border rounded"
                    style={{ borderColor: '#e2e8f0' }}
                  >
                    <h6 className="mb-1 fw-semibold">{event.title}</h6>
                    <p className="text-muted mb-1 small">{event.location}</p>
                    <small className="text-muted">
                      {new Date(event.event_date).toLocaleDateString()}
                    </small>
                  </div>
                ))
              ) : (
                <div className="text-center py-4 text-muted">
                  <p>No events available</p>
                  <small>Events will appear here when created</small>
                </div>
              )}
            </Card.Body>
          </Card>
        </Col>

        {/* Revenue Chart */}
        <Col xs={12} lg={6}>
          <Card>
            <Card.Header>
              <h6 className="mb-0 fw-semibold">Monthly Revenue</h6>
              <small className="text-muted">Revenue breakdown by month</small>
            </Card.Header>
            <Card.Body>
              <ResponsiveContainer width="100%" height={250}>
                <BarChart data={bookingTrends}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                  <XAxis 
                    dataKey="name" 
                    stroke="#64748b"
                    fontSize={12}
                  />
                  <YAxis stroke="#64748b" fontSize={12} />
                  <Tooltip
                    contentStyle={{
                      background: 'rgba(255, 255, 255, 0.95)',
                      backdropFilter: 'blur(20px)',
                      border: 'none',
                      borderRadius: '12px',
                      boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)',
                    }}
                  />
                  <Bar
                    dataKey="revenue"
                    fill="url(#colorRevenue)"
                    radius={[4, 4, 0, 0]}
                  />
                  <defs>
                    <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#10b981" stopOpacity={0.8}/>
                      <stop offset="95%" stopColor="#10b981" stopOpacity={0.3}/>
                    </linearGradient>
                  </defs>
                </BarChart>
              </ResponsiveContainer>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default Dashboard;