import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Card, Button, Alert, Badge } from 'react-bootstrap';

interface LogEntry {
  timestamp: string;
  level: string;
  message: string;
  context?: any;
}

interface ApiError {
  timestamp: string;
  url: string;
  method: string;
  status: number;
  error: string;
  request_data?: any;
  response_data?: any;
}

const FrontendLogViewer: React.FC = () => {
  const [apiErrors, setApiErrors] = useState<ApiError[]>([]);
  const [logEntries, setLogEntries] = useState<LogEntry[]>([]);
  const [backendLogs, setBackendLogs] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  // Load stored frontend errors from localStorage
  useEffect(() => {
    loadFrontendErrors();
    loadFrontendLogs();
  }, []);

  const loadFrontendErrors = () => {
    try {
      const stored = localStorage.getItem('kuantan188_api_errors');
      if (stored) {
        const errors = JSON.parse(stored);
        setApiErrors(errors.slice(-50)); // Last 50 errors
      }
    } catch (e) {
      console.error('Failed to load frontend errors:', e);
    }
  };

  const loadFrontendLogs = () => {
    try {
      const stored = localStorage.getItem('kuantan188_frontend_logs');
      if (stored) {
        const logs = JSON.parse(stored);
        setLogEntries(logs.slice(-100)); // Last 100 log entries
      }
    } catch (e) {
      console.error('Failed to load frontend logs:', e);
    }
  };

  const loadBackendLogs = async (lines: number = 50) => {
    try {
      setLoading(true);
      
      const backendUrl = process.env.NODE_ENV === 'production' 
        ? 'https://admin.tfcmockup.com/debug-logs.php'
        : 'http://localhost:8000/debug-logs.php';
        
      const response = await fetch(`${backendUrl}?lines=${lines}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      setBackendLogs(data);
    } catch (error) {
      console.error('Failed to load backend logs:', error);
      setBackendLogs({
        error: error instanceof Error ? error.message : 'Failed to load backend logs'
      });
    } finally {
      setLoading(false);
    }
  };

  const clearFrontendLogs = () => {
    localStorage.removeItem('kuantan188_api_errors');
    localStorage.removeItem('kuantan188_frontend_logs');
    setApiErrors([]);
    setLogEntries([]);
  };

  const clearBackendLogs = async () => {
    try {
      const backendUrl = process.env.NODE_ENV === 'production' 
        ? 'https://admin.tfcmockup.com/clear-logs.php'
        : 'http://localhost:8000/clear-logs.php';
        
      const response = await fetch(backendUrl, { method: 'POST' });
      if (response.ok) {
        setBackendLogs(null);
        alert('Backend logs cleared successfully');
      } else {
        alert('Failed to clear backend logs');
      }
    } catch (error) {
      alert('Failed to clear backend logs: ' + error);
    }
  };

  const getLogLevelColor = (level: string) => {
    switch (level.toUpperCase()) {
      case 'ERROR': return 'danger';
      case 'WARNING': return 'warning';
      case 'INFO': return 'info';
      case 'DEBUG': return 'secondary';
      default: return 'light';
    }
  };

  const formatTimestamp = (timestamp: string) => {
    return new Date(timestamp).toLocaleString();
  };

  return (
    <Container className="py-4">
      <Row>
        <Col>
          <h2 className="mb-4">🔍 Debug Log Viewer</h2>
          
          {/* Control Panel */}
          <Card className="mb-4">
            <Card.Header>
              <h5>Log Controls</h5>
            </Card.Header>
            <Card.Body>
              <div className="d-flex flex-wrap gap-2">
                <Button 
                  variant="primary" 
                  onClick={() => loadBackendLogs(25)}
                  disabled={loading}
                >
                  Load Backend Logs (25)
                </Button>
                <Button 
                  variant="primary" 
                  onClick={() => loadBackendLogs(50)}
                  disabled={loading}
                >
                  Load Backend Logs (50)
                </Button>
                <Button 
                  variant="primary" 
                  onClick={() => loadBackendLogs(100)}
                  disabled={loading}
                >
                  Load Backend Logs (100)
                </Button>
                <Button variant="info" onClick={loadFrontendLogs}>
                  Refresh Frontend Logs
                </Button>
                <Button variant="warning" onClick={clearFrontendLogs}>
                  Clear Frontend Logs
                </Button>
                <Button variant="danger" onClick={clearBackendLogs}>
                  Clear Backend Logs
                </Button>
              </div>
              {loading && (
                <div className="mt-2">
                  <small className="text-muted">Loading backend logs...</small>
                </div>
              )}
            </Card.Body>
          </Card>

          {/* Frontend API Errors */}
          <Card className="mb-4">
            <Card.Header className="d-flex justify-content-between align-items-center">
              <h5>Frontend API Errors ({apiErrors.length})</h5>
              <Badge bg="danger">{apiErrors.length}</Badge>
            </Card.Header>
            <Card.Body style={{ maxHeight: '400px', overflow: 'auto' }}>
              {apiErrors.length === 0 ? (
                <p className="text-muted">No API errors recorded</p>
              ) : (
                apiErrors.slice(-10).reverse().map((error, index) => (
                  <Alert key={index} variant="danger" className="mb-2">
                    <div className="d-flex justify-content-between">
                      <strong>{error.method} {error.url}</strong>
                      <small>{formatTimestamp(error.timestamp)}</small>
                    </div>
                    <div>Status: {error.status} - {error.error}</div>
                    {error.request_data && (
                      <details className="mt-2">
                        <summary>Request Data</summary>
                        <pre className="mt-1" style={{ fontSize: '0.8rem' }}>
                          {JSON.stringify(error.request_data, null, 2)}
                        </pre>
                      </details>
                    )}
                    {error.response_data && (
                      <details className="mt-2">
                        <summary>Response Data</summary>
                        <pre className="mt-1" style={{ fontSize: '0.8rem' }}>
                          {JSON.stringify(error.response_data, null, 2)}
                        </pre>
                      </details>
                    )}
                  </Alert>
                ))
              )}
            </Card.Body>
          </Card>

          {/* Backend Logs */}
          {backendLogs && (
            <Card className="mb-4">
              <Card.Header>
                <h5>Backend Logs</h5>
                {backendLogs.total_lines && (
                  <small className="text-muted">
                    Showing {backendLogs.showing_lines} of {backendLogs.total_lines} total lines 
                    (Last updated: {backendLogs.timestamp})
                  </small>
                )}
              </Card.Header>
              <Card.Body style={{ maxHeight: '500px', overflow: 'auto' }}>
                {backendLogs.error ? (
                  <Alert variant="danger">
                    <strong>Error loading backend logs:</strong> {backendLogs.error}
                  </Alert>
                ) : (
                  <div style={{ fontFamily: 'monospace', fontSize: '0.85rem' }}>
                    {backendLogs.last_lines?.map((line: string, index: number) => {
                      let variant = '';
                      if (line.includes('ERROR') || line.includes('Exception')) variant = 'danger';
                      else if (line.includes('WARNING')) variant = 'warning';
                      else if (line.includes('INFO')) variant = 'info';
                      
                      return (
                        <div 
                          key={index} 
                          className={`p-1 mb-1 rounded ${variant ? `bg-${variant} bg-opacity-10` : ''}`}
                          style={{ fontSize: '0.75rem' }}
                        >
                          {line}
                        </div>
                      );
                    })}
                  </div>
                )}
              </Card.Body>
            </Card>
          )}

          {/* Frontend Console Logs */}
          <Card>
            <Card.Header>
              <h5>Frontend Console Logs ({logEntries.length})</h5>
            </Card.Header>
            <Card.Body style={{ maxHeight: '400px', overflow: 'auto' }}>
              {logEntries.length === 0 ? (
                <p className="text-muted">No frontend logs recorded</p>
              ) : (
                logEntries.slice(-20).reverse().map((log, index) => (
                  <Alert key={index} variant={getLogLevelColor(log.level)} className="mb-2">
                    <div className="d-flex justify-content-between">
                      <Badge bg={getLogLevelColor(log.level)}>{log.level}</Badge>
                      <small>{formatTimestamp(log.timestamp)}</small>
                    </div>
                    <div className="mt-1">{log.message}</div>
                    {log.context && (
                      <details className="mt-2">
                        <summary>Context</summary>
                        <pre className="mt-1" style={{ fontSize: '0.8rem' }}>
                          {JSON.stringify(log.context, null, 2)}
                        </pre>
                      </details>
                    )}
                  </Alert>
                ))
              )}
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default FrontendLogViewer;