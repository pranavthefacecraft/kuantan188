import React, { useState } from 'react';
import { Container, Row, Col, Card, Form, Button, Alert } from 'react-bootstrap';

const Contact: React.FC = () => {
  const [showAlert, setShowAlert] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: ''
  });

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Here you would typically send the form data to your backend
    console.log('Form submitted:', formData);
    setShowAlert(true);
    // Reset form
    setFormData({
      name: '',
      email: '',
      subject: '',
      message: ''
    });
    // Hide alert after 5 seconds
    setTimeout(() => setShowAlert(false), 5000);
  };

  return (
    <Container className="py-5">
      {/* Header Section */}
      <Row className="mb-5">
        <Col lg={8} className="mx-auto text-center">
          <h1 className="fw-bold mb-4">Contact Us</h1>
          <p className="lead text-muted">
            Have questions or need assistance? We're here to help! Reach out to us 
            and we'll get back to you as soon as possible.
          </p>
        </Col>
      </Row>

      {/* Success Alert */}
      {showAlert && (
        <Row className="mb-4">
          <Col>
            <Alert variant="success" dismissible onClose={() => setShowAlert(false)}>
              <Alert.Heading>Message Sent Successfully!</Alert.Heading>
              Thank you for contacting us. We'll get back to you within 24 hours.
            </Alert>
          </Col>
        </Row>
      )}

      <Row>
        {/* Contact Form */}
        <Col lg={8} className="mb-5">
          <Card className="border-0 shadow-sm">
            <Card.Body className="p-4">
              <h3 className="fw-bold mb-4">Send Us a Message</h3>
              <Form onSubmit={handleSubmit}>
                <Row>
                  <Col md={6} className="mb-3">
                    <Form.Group>
                      <Form.Label>Full Name *</Form.Label>
                      <Form.Control
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleInputChange}
                        placeholder="Enter your full name"
                        required
                      />
                    </Form.Group>
                  </Col>
                  <Col md={6} className="mb-3">
                    <Form.Group>
                      <Form.Label>Email Address *</Form.Label>
                      <Form.Control
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleInputChange}
                        placeholder="Enter your email"
                        required
                      />
                    </Form.Group>
                  </Col>
                </Row>
                
                <Form.Group className="mb-3">
                  <Form.Label>Subject *</Form.Label>
                  <Form.Select
                    name="subject"
                    value={formData.subject}
                    onChange={handleInputChange}
                    required
                  >
                    <option value="">Select a subject</option>
                    <option value="general">General Inquiry</option>
                    <option value="booking">Booking Support</option>
                    <option value="refund">Refund Request</option>
                    <option value="technical">Technical Issue</option>
                    <option value="partnership">Partnership</option>
                    <option value="feedback">Feedback</option>
                  </Form.Select>
                </Form.Group>
                
                <Form.Group className="mb-4">
                  <Form.Label>Message *</Form.Label>
                  <Form.Control
                    as="textarea"
                    rows={5}
                    name="message"
                    value={formData.message}
                    onChange={handleInputChange}
                    placeholder="Tell us how we can help you..."
                    required
                  />
                </Form.Group>
                
                <Button variant="primary" type="submit" size="lg">
                  Send Message
                </Button>
              </Form>
            </Card.Body>
          </Card>
        </Col>

        {/* Contact Information */}
        <Col lg={4}>
          <Card className="border-0 shadow-sm mb-4">
            <Card.Body className="p-4">
              <h5 className="fw-bold mb-3">📞 Phone Support</h5>
              <p className="text-muted mb-2">
                <strong>Customer Service:</strong><br />
                +60 12-345-6789
              </p>
              <p className="text-muted mb-0">
                <strong>Hours:</strong><br />
                Mon-Fri: 9:00 AM - 6:00 PM<br />
                Sat-Sun: 10:00 AM - 4:00 PM
              </p>
            </Card.Body>
          </Card>

          <Card className="border-0 shadow-sm mb-4">
            <Card.Body className="p-4">
              <h5 className="fw-bold mb-3">✉️ Email Support</h5>
              <p className="text-muted mb-2">
                <strong>General Inquiries:</strong><br />
                info@kuantan188.com
              </p>
              <p className="text-muted mb-2">
                <strong>Technical Support:</strong><br />
                support@kuantan188.com
              </p>
              <p className="text-muted mb-0">
                <strong>Partnerships:</strong><br />
                partners@kuantan188.com
              </p>
            </Card.Body>
          </Card>

          <Card className="border-0 shadow-sm mb-4">
            <Card.Body className="p-4">
              <h5 className="fw-bold mb-3">📍 Office Location</h5>
              <p className="text-muted mb-3">
                <strong>Kuantan188 HQ</strong><br />
                123 Jalan Sultan Ahmad Shah<br />
                25000 Kuantan, Pahang<br />
                Malaysia
              </p>
              <Button variant="outline-primary" size="sm">
                Get Directions
              </Button>
            </Card.Body>
          </Card>

          <Card className="border-0 shadow-sm">
            <Card.Body className="p-4">
              <h5 className="fw-bold mb-3">🌐 Follow Us</h5>
              <div className="d-flex gap-3">
                <Button variant="outline-primary" size="sm">
                  Facebook
                </Button>
                <Button variant="outline-primary" size="sm">
                  Instagram
                </Button>
                <Button variant="outline-primary" size="sm">
                  Twitter
                </Button>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>

      {/* FAQ Section */}
      <Row className="mt-5 py-5 bg-light rounded-4">
        <Col>
          <h3 className="text-center fw-bold mb-5">Frequently Asked Questions</h3>
          <Row>
            <Col lg={6} className="mb-4">
              <Card className="border-0 h-100">
                <Card.Body>
                  <Card.Title className="fw-bold mb-3">How do I cancel my ticket?</Card.Title>
                  <Card.Text className="text-muted">
                    You can cancel your ticket up to 24 hours before the event. 
                    Visit your account dashboard or contact our support team for assistance.
                  </Card.Text>
                </Card.Body>
              </Card>
            </Col>
            <Col lg={6} className="mb-4">
              <Card className="border-0 h-100">
                <Card.Body>
                  <Card.Title className="fw-bold mb-3">What payment methods do you accept?</Card.Title>
                  <Card.Text className="text-muted">
                    We accept all major credit cards, online banking, and e-wallet payments 
                    including GrabPay, Touch 'n Go, and Boost.
                  </Card.Text>
                </Card.Body>
              </Card>
            </Col>
            <Col lg={6} className="mb-4">
              <Card className="border-0 h-100">
                <Card.Body>
                  <Card.Title className="fw-bold mb-3">How will I receive my tickets?</Card.Title>
                  <Card.Text className="text-muted">
                    Digital tickets will be sent to your email immediately after purchase. 
                    You can also access them through your account dashboard.
                  </Card.Text>
                </Card.Body>
              </Card>
            </Col>
            <Col lg={6} className="mb-4">
              <Card className="border-0 h-100">
                <Card.Body>
                  <Card.Title className="fw-bold mb-3">Can I transfer my ticket to someone else?</Card.Title>
                  <Card.Text className="text-muted">
                    Yes, tickets can be transferred through your account dashboard. 
                    The new recipient will receive their own digital ticket.
                  </Card.Text>
                </Card.Body>
              </Card>
            </Col>
          </Row>
        </Col>
      </Row>

      {/* Emergency Contact */}
      <Row className="mt-5">
        <Col className="text-center">
          <Alert variant="info">
            <Alert.Heading>Need Immediate Assistance?</Alert.Heading>
            For urgent matters during events, please call our 24/7 emergency hotline: 
            <strong> +60 11-1234-5678</strong>
          </Alert>
        </Col>
      </Row>
    </Container>
  );
};

export default Contact;