import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
import Home from './pages/Home';
import Events from './pages/Events';
import EventDetail from './pages/EventDetail';
import About from './pages/About';
import Contact from './pages/Contact';
import Tickets from './pages/Tickets';
import TicketDetail from './pages/TicketDetail';
import TicketsTestPage from './pages/TicketsTestPage';
import ApiTestPage from './components/ApiTestPage';
import PaymentCallback from './pages/PaymentCallback';
import LogViewer from './pages/LogViewer';
import SentryTest from './pages/SentryTest';
import Layout from './components/layout/Layout';
import './App.css';

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          <Route path="/" element={<Layout />}>
            <Route index element={<Home />} />
            <Route path="events" element={<Events />} />
            <Route path="events/:id" element={<EventDetail />} />
            <Route path="tickets" element={<Tickets />} />
            <Route path="tickets/:id" element={<TicketDetail />} />
            <Route path="tickets-test" element={<TicketsTestPage />} />
            <Route path="about" element={<About />} />
            <Route path="contact" element={<Contact />} />
            <Route path="payment/callback" element={<PaymentCallback />} />
            <Route path="logs" element={<LogViewer />} />
            <Route path="api-test" element={<ApiTestPage />} />
            <Route path="sentry-test" element={<SentryTest />} />
          </Route>
        </Routes>
      </div>
    </Router>
  );
}

export default App;
