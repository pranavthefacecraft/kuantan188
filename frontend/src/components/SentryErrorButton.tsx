import React from 'react';
import * as Sentry from '@sentry/react';

/**
 * Test button to verify Sentry error tracking
 * Add this component to any page to test Sentry integration
 */
function SentryErrorButton() {
  return (
    <button
      className="btn btn-danger"
      onClick={() => {
        throw new Error('This is your first error!');
      }}
    >
      🔥 Break the world (Test Sentry)
    </button>
  );
}

export default SentryErrorButton;
