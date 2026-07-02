import React from 'react';
import * as Sentry from '@sentry/react';
import { Container, Button, Card, Alert } from 'react-bootstrap';

const SentryTest: React.FC = () => {
  const triggerError = () => {
    try {
      // This will throw an error and be captured by Sentry
      throw new Error('Test Error: Sentry is working! Triggered at ' + new Date().toISOString());
    } catch (error) {
      Sentry.captureException(error);
      alert('Error sent to Sentry! Check your Sentry dashboard.');
    }
  };

  const triggerUncaughtError = () => {
    // This will cause an uncaught error
    throw new Error('Uncaught Error: Testing Sentry error boundary at ' + new Date().toISOString());
  };

  const sendTestMessage = () => {
    Sentry.captureMessage('Test Message: Sentry messaging is working!', 'info');
    alert('Message sent to Sentry!');
  };

  const sendBreadcrumb = () => {
    Sentry.addBreadcrumb({
      message: 'User clicked test button',
      level: 'info',
      category: 'user-action',
    });
    alert('Breadcrumb added! It will appear with the next error.');
  };

  return (
    <Container className="py-5">
      <h1 className="mb-4">🔍 Sentry Error Tracking Test</h1>
      
      <Alert variant="info">
        <strong>Sentry Status:</strong> Configured and running<br />
        <strong>Environment:</strong> {process.env.REACT_APP_ENVIRONMENT || 'development'}<br />
        <strong>DSN:</strong> {process.env.REACT_APP_SENTRY_DSN ? '✓ Configured' : '✗ Not configured'}
      </Alert>

      <Card className="mb-3">
        <Card.Body>
          <Card.Title>Test 1: Caught Error</Card.Title>
          <Card.Text>
            This will throw an error that's caught and manually sent to Sentry.
          </Card.Text>
          <Button variant="primary" onClick={triggerError}>
            Trigger Caught Error
          </Button>
        </Card.Body>
      </Card>

      <Card className="mb-3">
        <Card.Body>
          <Card.Title>Test 2: Uncaught Error (Error Boundary)</Card.Title>
          <Card.Text>
            This will throw an uncaught error that should be caught by Sentry's Error Boundary.
          </Card.Text>
          <Button variant="danger" onClick={triggerUncaughtError}>
            Trigger Uncaught Error
          </Button>
        </Card.Body>
      </Card>

      <Card className="mb-3">
        <Card.Body>
          <Card.Title>Test 3: Message Capture</Card.Title>
          <Card.Text>
            Send a custom message to Sentry (not an error).
          </Card.Text>
          <Button variant="success" onClick={sendTestMessage}>
            Send Test Message
          </Button>
        </Card.Body>
      </Card>

      <Card className="mb-3">
        <Card.Body>
          <Card.Title>Test 4: Add Breadcrumb</Card.Title>
          <Card.Text>
            Add a breadcrumb that will appear with the next error for context.
          </Card.Text>
          <Button variant="info" onClick={sendBreadcrumb}>
            Add Breadcrumb
          </Button>
        </Card.Body>
      </Card>

      <Alert variant="warning" className="mt-4">
        <strong>Note:</strong> After triggering errors, check your Sentry dashboard at{' '}
        <a href="https://sentry.io/" target="_blank" rel="noopener noreferrer">
          sentry.io
        </a>{' '}
        to see the captured events.
      </Alert>
    </Container>
  );
};

export default SentryTest;
