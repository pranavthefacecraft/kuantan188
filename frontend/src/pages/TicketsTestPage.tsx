import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Card, Button, Table, Alert, Spinner } from 'react-bootstrap';
import { eventsApi } from '../services/api';

interface TicketData {
  id: number;
  title?: string;
  name?: string;
  description?: string;
  adult_price?: number;
  child_price?: number;
  price?: number;
  image_url?: string;
  event_id?: number;
  country_id?: number;
  is_active?: boolean;
  event?: any;
  country?: any;
  [key: string]: any; // For any additional fields
}

const TicketsTestPage: React.FC = () => {
  const [tickets, setTickets] = useState<TicketData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [rawApiResponse, setRawApiResponse] = useState<any>(null);

  useEffect(() => {
    const fetchTickets = async () => {
      try {
        setLoading(true);
        console.log('🎫 Fetching tickets from API...');
        
        const response = await eventsApi.getTickets();
        console.log('📋 Full API Response:', response);
        
        setRawApiResponse(response);
        
        if (response.success) {
          setTickets(response.data || []);
          console.log('✅ Tickets loaded:', response.data);
        } else {
          setError('Failed to load tickets');
          console.error('❌ API returned success: false');
        }
      } catch (err) {
        console.error('🚨 Error fetching tickets:', err);
        setError(`Error: ${err}`);
      } finally {
        setLoading(false);
      }
    };

    fetchTickets();
  }, []);

  const renderFieldValue = (value: any) => {
    if (value === null || value === undefined) return <span className="text-muted">null</span>;
    if (typeof value === 'object') return <pre>{JSON.stringify(value, null, 2)}</pre>;
    return String(value);
  };

  const getAllFields = (tickets: TicketData[]) => {
    const allFields = new Set<string>();
    tickets.forEach(ticket => {
      Object.keys(ticket).forEach(key => allFields.add(key));
    });
    return Array.from(allFields).sort();
  };

  if (loading) {
    return (
      <Container className="py-5">
        <div className="text-center">
          <Spinner animation="border" variant="primary" />
          <p className="mt-3">Loading tickets data...</p>
        </div>
      </Container>
    );
  }

  return (
    <Container className="py-5">
      <Row>
        <Col>
          <h1 className="mb-4">🎫 Tickets API Test Page</h1>
          
          {error && (
            <Alert variant="danger">
              <Alert.Heading>Error Loading Tickets</Alert.Heading>
              <p>{error}</p>
            </Alert>
          )}

          {/* API Response Summary */}
          <Card className="mb-4">
            <Card.Header>
              <h5>📊 API Response Summary</h5>
            </Card.Header>
            <Card.Body>
              <Row>
                <Col md={6}>
                  <strong>API Success:</strong> {rawApiResponse?.success ? '✅ True' : '❌ False'}
                </Col>
                <Col md={6}>
                  <strong>Total Tickets:</strong> {tickets.length}
                </Col>
              </Row>
              <hr />
              <h6>Raw API Response:</h6>
              <pre style={{ backgroundColor: '#f8f9fa', padding: '10px', borderRadius: '5px', fontSize: '12px' }}>
                {JSON.stringify(rawApiResponse, null, 2)}
              </pre>
            </Card.Body>
          </Card>

          {/* Tickets Data Table */}
          {tickets.length > 0 ? (
            <Card className="mb-4">
              <Card.Header>
                <h5>🗂️ All Ticket Fields</h5>
              </Card.Header>
              <Card.Body>
                <div className="table-responsive">
                  <Table striped bordered hover size="sm">
                    <thead>
                      <tr>
                        <th>Field Name</th>
                        {tickets.map((ticket, index) => (
                          <th key={index}>Ticket {index + 1} (ID: {ticket.id || 'N/A'})</th>
                        ))}
                      </tr>
                    </thead>
                    <tbody>
                      {getAllFields(tickets).map(field => (
                        <tr key={field}>
                          <td><strong>{field}</strong></td>
                          {tickets.map((ticket, index) => (
                            <td key={index} style={{ maxWidth: '200px', wordWrap: 'break-word' }}>
                              {renderFieldValue(ticket[field])}
                            </td>
                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </div>
              </Card.Body>
            </Card>
          ) : (
            <Alert variant="warning">
              <Alert.Heading>No Tickets Found</Alert.Heading>
              <p>The API returned no tickets or an empty array.</p>
            </Alert>
          )}

          {/* Key Fields Analysis */}
          {tickets.length > 0 && (
            <Card className="mb-4">
              <Card.Header>
                <h5>🔍 Key Fields Analysis</h5>
              </Card.Header>
              <Card.Body>
                <Row>
                  <Col md={12}>
                    <h6>Title/Name Fields:</h6>
                    {tickets.map((ticket, index) => (
                      <div key={index} className="mb-2">
                        <strong>Ticket {index + 1}:</strong>
                        <ul>
                          <li><strong>title:</strong> {renderFieldValue(ticket.title)}</li>
                          <li><strong>name:</strong> {renderFieldValue(ticket.name)}</li>
                        </ul>
                      </div>
                    ))}
                  </Col>
                </Row>
                <hr />
                <Row>
                  <Col md={12}>
                    <h6>Price Fields:</h6>
                    {tickets.map((ticket, index) => (
                      <div key={index} className="mb-2">
                        <strong>Ticket {index + 1}:</strong>
                        <ul>
                          <li><strong>adult_price:</strong> {renderFieldValue(ticket.adult_price)}</li>
                          <li><strong>child_price:</strong> {renderFieldValue(ticket.child_price)}</li>
                          <li><strong>price:</strong> {renderFieldValue(ticket.price)}</li>
                        </ul>
                      </div>
                    ))}
                  </Col>
                </Row>
              </Card.Body>
            </Card>
          )}

          {/* Test Different API Endpoints */}
          <Card>
            <Card.Header>
              <h5>🧪 Test Different API Calls</h5>
            </Card.Header>
            <Card.Body>
              <Button 
                variant="primary" 
                className="me-2 mb-2"
                onClick={() => window.location.reload()}
              >
                🔄 Reload Tickets
              </Button>
              <Button 
                variant="info" 
                className="me-2 mb-2"
                onClick={() => {
                  fetch('/api/tickets')
                    .then(res => res.json())
                    .then(data => {
                      console.log('Direct /api/tickets:', data);
                      alert('Check console for direct API response');
                    })
                    .catch(err => console.error('Direct API error:', err));
                }}
              >
                🔗 Test Direct /api/tickets
              </Button>
              <Button 
                variant="secondary"
                onClick={() => {
                  console.log('Current tickets state:', tickets);
                  console.log('Raw API response:', rawApiResponse);
                }}
              >
                📋 Log to Console
              </Button>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default TicketsTestPage;