import React, { useState, useEffect } from 'react';
import { Modal, Button, Row, Col, Form } from 'react-bootstrap';
import { format, addDays } from 'date-fns';
import PaymentMethodSelector, { PaymentMethod } from './PaymentMethodSelector';
import paymentApi, { BookingData } from '../../services/paymentApi';
import PaymentLoading from '../loading/PaymentLoading';
import frontendLogger from '../../utils/logger';
import { eventsApi } from '../../services/api';

interface Ticket {
  id: number;
  event_id?: number;
  title?: string;
  name?: string;
  description?: string;
  adult_price?: string;
  child_price?: string;
  price?: string;
  image_url?: string;
  total_quantity?: number;
  available_quantity?: number;
  pricing?: {
    malaysian: {
      adult_price: string;
      child_price: string;
      available: boolean;
    };
    non_malaysian: {
      adult_price: string;
      child_price: string;
      available: boolean;
    };
  };
  // Legacy support
  countries?: Array<{
    id: number;
    name: string;
    currency_symbol: string;
    adult_price: string;
    teen_price?: string;
    university_price?: string;
    child_price: string;
  }>;
}

interface TicketBookingModalProps {
  show: boolean;
  onHide: () => void;
  ticket: Ticket | null;
}

const TicketBookingModal: React.FC<TicketBookingModalProps> = ({ show, onHide, ticket }) => {
  const [currentStep, setCurrentStep] = useState<'selection' | 'details' | 'payment' | 'thankyou'>('selection');
  const [selectedCountry, setSelectedCountry] = useState<any>(null);
  const [activeTab, setActiveTab] = useState<'malaysian' | 'non_malaysian'>('malaysian');
  
  // Separate quantities for Malaysian and Non-Malaysian
  const [malaysianQuantities, setMalaysianQuantities] = useState({
    adult: 1,
    teenager: 0,
    university: 0,
    child: 0
  });
  
  const [nonMalaysianQuantities, setNonMalaysianQuantities] = useState({
    adult: 0,
    teenager: 0,
    university: 0,
    child: 0
  });
  
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [showCalendar, setShowCalendar] = useState(false);
  const [bookingResult, setBookingResult] = useState<any>(null);
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cash_on_delivery');
  const [isProcessingPayment, setIsProcessingPayment] = useState(false);
  
  // Currency state
  const [currencies, setCurrencies] = useState<any[]>([]);
  const [selectedCurrency, setSelectedCurrency] = useState<any>(null);
  
  // Contact form state
  const [contactForm, setContactForm] = useState({
    firstName: '',
    lastName: '',
    email: '',
    mobilePhone: '',
    country: '',
    postalCode: '',
    receiveUpdates: false
  });

  // Reset modal state when it opens/closes
  React.useEffect(() => {
    if (show && ticket) {
      console.log('Modal opened with ticket:', ticket); // Debug log
      setCurrentStep('selection');
      setSelectedCountry(ticket.countries?.[0] || null);
      setActiveTab('malaysian');
      setMalaysianQuantities({ adult: 1, teenager: 0, university: 0, child: 0 });
      setNonMalaysianQuantities({ adult: 0, teenager: 0, university: 0, child: 0 });
      setSelectedDate(new Date());
      setSelectedTime('');
      setShowCalendar(false);
      setContactForm({
        firstName: '',
        lastName: '',
        email: '',
        mobilePhone: '',
        country: '',
        postalCode: '',
        receiveUpdates: false
      });
      setPaymentMethod('cash_on_delivery');
      setIsProcessingPayment(false);
    }
  }, [show, ticket]);

  // Fetch currencies when component mounts
  React.useEffect(() => {
    const fetchCurrencies = async () => {
      try {
        const response = await eventsApi.getCurrencies();
        const currencyData = response.data || [];
        setCurrencies(currencyData);
        // Set default currency (first one or USD if available)
        const defaultCurrency = currencyData.find((curr: any) => curr.currency_code === 'USD') || currencyData[0];
        setSelectedCurrency(defaultCurrency);
      } catch (error) {
        console.error('Failed to fetch currencies:', error);
        // Set fallback currency
        setSelectedCurrency({ currency_code: 'USD', currency_symbol: '$', country_name: 'United States' });
      }
    };

    if (show) {
      fetchCurrencies();
    }
  }, [show]);

  const handleQuantityChange = (type: 'adult' | 'teenager' | 'university' | 'child', change: number) => {
    const currentQuantities = getCurrentQuantities();
    const newQuantity = currentQuantities[type] + change;
    
    if (newQuantity >= 0) {
      const updatedQuantities = {
        ...currentQuantities,
        [type]: newQuantity
      };
      updateCurrentQuantities(updatedQuantities);
    }
  };

  const calculateTotal = () => {
    let malayTotal = 0;
    let nonMalayTotal = 0;
    
    if (ticket?.pricing) {
      // Calculate Malaysian total
      const malayPricing = ticket.pricing.malaysian;
      const malayAdultPrice = parseFloat(malayPricing.adult_price || '0');
      const malayChildPrice = parseFloat(malayPricing.child_price || '0');
      
      malayTotal = (malayAdultPrice * malaysianQuantities.adult) + 
                  (malayAdultPrice * malaysianQuantities.teenager) + // Same as adult
                  (malayAdultPrice * malaysianQuantities.university) + // Same as adult
                  (malayChildPrice * malaysianQuantities.child);
                  
      // Calculate Non-Malaysian total
      const nonMalayPricing = ticket.pricing.non_malaysian;
      const nonMalayAdultPrice = parseFloat(nonMalayPricing.adult_price || '0');
      const nonMalayChildPrice = parseFloat(nonMalayPricing.child_price || '0');
      
      nonMalayTotal = (nonMalayAdultPrice * nonMalaysianQuantities.adult) + 
                     (nonMalayAdultPrice * nonMalaysianQuantities.teenager) + // Same as adult
                     (nonMalayAdultPrice * nonMalaysianQuantities.university) + // Same as adult
                     (nonMalayChildPrice * nonMalaysianQuantities.child);
    } else if (selectedCountry) {
      // Legacy fallback
      const baseAdultPrice = parseFloat(selectedCountry.adult_price || '0');
      const baseChildPrice = parseFloat(selectedCountry.child_price || '0');
      malayTotal = (baseAdultPrice * getCurrentQuantities().adult) + (baseChildPrice * getCurrentQuantities().child);
    }
    
    let totalInMYR = malayTotal + nonMalayTotal;
    
    // Apply currency conversion if a currency is selected
    if (selectedCurrency && selectedCurrency.exchange_rate && selectedCurrency.currency_code !== 'MYR') {
      totalInMYR = totalInMYR / parseFloat(selectedCurrency.exchange_rate);
    }
    
    return totalInMYR;
  };

  // Helper function to get current tab quantities
  const getCurrentQuantities = () => {
    return activeTab === 'malaysian' ? malaysianQuantities : nonMalaysianQuantities;
  };
  
  // Helper function to update current tab quantities
  const updateCurrentQuantities = (newQuantities: any) => {
    if (activeTab === 'malaysian') {
      setMalaysianQuantities(newQuantities);
    } else {
      setNonMalaysianQuantities(newQuantities);
    }
  };

  const getTotalQuantity = () => {
    const malayTotal = malaysianQuantities.adult + malaysianQuantities.teenager + 
                      malaysianQuantities.university + malaysianQuantities.child;
    const nonMalayTotal = nonMalaysianQuantities.adult + nonMalaysianQuantities.teenager + 
                         nonMalaysianQuantities.university + nonMalaysianQuantities.child;
    return malayTotal + nonMalayTotal;
  };

  // Helper function to get price and currency for display
  const getPrice = (type: 'adult' | 'child') => {
    let basePriceMYR = 0;
    
    if (ticket?.pricing) {
      const pricing = activeTab === 'malaysian' ? ticket.pricing.malaysian : ticket.pricing.non_malaysian;
      const priceValue = type === 'adult' ? pricing.adult_price : pricing.child_price;
      basePriceMYR = parseFloat(priceValue || '0');
    } else if (selectedCountry) {
      basePriceMYR = parseFloat(selectedCountry[`${type}_price`] || '0');
    } else {
      basePriceMYR = type === 'adult' ? 49 : 35;
    }
    
    // Apply currency conversion if a currency is selected
    let convertedPrice = basePriceMYR;
    if (selectedCurrency && selectedCurrency.exchange_rate && selectedCurrency.currency_code !== 'MYR') {
      convertedPrice = basePriceMYR / parseFloat(selectedCurrency.exchange_rate);
    }
    
    return { 
      price: convertedPrice, 
      currency: selectedCurrency?.currency_symbol || 'RM' 
    };
  };

  // Helper function to get currency symbol
  const getCurrencySymbol = () => {
    return selectedCurrency?.currency_symbol || 'RM'; // Use selected currency or default to RM
  };

  // Helper function to get minimum price based on selected tab
  const getMinPrice = () => {
    if (!ticket?.pricing) {
      console.log('No pricing data found, using fallback');
      return '49'; // fallback to original price
    }
    
    const pricing = activeTab === 'malaysian' ? ticket.pricing.malaysian : ticket.pricing.non_malaysian;
    console.log('Getting min price for tab:', activeTab, 'Pricing:', pricing);
    
    // Get the lowest price available (child price is usually lower)
    const adultPrice = pricing.adult_price ? parseFloat(pricing.adult_price) : 0;
    const childPrice = pricing.child_price ? parseFloat(pricing.child_price) : 0;
    
    console.log('Adult price:', adultPrice, 'Child price:', childPrice);
    
    if (adultPrice === 0 && childPrice === 0) {
      console.log('Both prices are 0, using fallback');
      return '49'; // fallback
    }
    
    // Return the minimum non-zero price
    if (childPrice > 0 && (childPrice < adultPrice || adultPrice === 0)) {
      console.log('Returning child price:', childPrice);
      return childPrice.toString();
    }
    console.log('Returning adult price:', adultPrice);
    return adultPrice.toString();
  };

  const handleContinueToDetails = () => {
    if (getTotalQuantity() === 0) {
      alert('Please select at least one ticket');
      return;
    }
    if (!selectedTime) {
      alert('Please select a time slot');
      return;
    }
    setCurrentStep('details');
  };

  const handlePaymentFlow = async () => {
    setIsProcessingPayment(true);
    
    const bookingData: BookingData = {
      ticket_id: ticket?.id || 1,
      event_id: ticket?.event_id || undefined,
      event_title: `${ticketName} - ${format(selectedDate, 'MMMM d, yyyy')}`,
      customer_name: `${contactForm.firstName} ${contactForm.lastName}`,
      email: contactForm.email,
      mobile_phone: contactForm.mobilePhone,
      country: contactForm.country || selectedCountry?.name || 'Malaysia',
      postal_code: contactForm.postalCode,
      adult_tickets: malaysianQuantities.adult + nonMalaysianQuantities.adult,
      child_tickets: malaysianQuantities.child + nonMalaysianQuantities.child,
      quantity: getTotalQuantity(),
      adult_price: parseFloat(selectedCountry?.adult_price || '0'),
      child_price: parseFloat(selectedCountry?.child_price || '0'),
      event_date: format(selectedDate, 'yyyy-MM-dd'),
      selected_time: selectedTime,
      total_amount: calculateTotal(),
      payment_method: paymentMethod,
      booking_status: paymentMethod === 'billplz' ? 'pending' : 'confirmed',
      receive_updates: contactForm.receiveUpdates
    };

    try {

      if (paymentMethod === 'billplz') {
        // Process online payment
        const { bookingResponse, paymentUrl } = await paymentApi.processPayment(bookingData);
        setBookingResult(bookingResponse);
        
        // Add callback URL with booking ID - Fix URL construction
        const callbackUrl = `${window.location.origin}/payment/callback?booking_id=${bookingResponse.booking.id}`;
        
        // Smart URL construction to handle existing query parameters
        const separator = paymentUrl.includes('?') ? '&' : '?';
        const fullPaymentUrl = `${paymentUrl}${separator}redirect_url=${encodeURIComponent(callbackUrl)}`;
        
        // Redirect to Billplz payment page
        window.location.href = fullPaymentUrl;
      } else {
        // Process cash on delivery
        const result = await paymentApi.processCashOnDelivery(bookingData);
        setBookingResult(result);
        setCurrentStep('thankyou');
      }
    } catch (error) {
      console.error('[PAYMENT FLOW] Error:', error);
      const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
      
      frontendLogger.logPaymentError('Modal Payment Flow', errorMessage, {
        paymentMethod,
        ticketName,
        totalAmount: calculateTotal(),
        bookingData
      });
      
      // Show detailed error message to user
      alert(`Payment failed: ${errorMessage}\n\nPlease check the logs at /logs for more details or contact support.`);
    } finally {
      setIsProcessingPayment(false);
    }
  };

  const handleContinueToPayment = () => {
    // Validate contact form
    if (!contactForm.firstName || !contactForm.lastName || !contactForm.email || !contactForm.mobilePhone) {
      alert('Please fill in all required fields');
      return;
    }
    setCurrentStep('payment');
  };

  const handlePayment = async () => {
    await handlePaymentFlow();
  };

  // Add debug logging when component mounts
  useEffect(() => {
    console.log('TicketBookingModal opened with ticket:', ticket);
    console.log('Active tab:', activeTab);
  }, [ticket, activeTab]);

  const handleClose = () => {
    setCurrentStep('selection');
    setBookingResult(null);
    setIsProcessingPayment(false);
    onHide();
  };

  if (!ticket) return null;

  const ticketName = ticket.title || ticket.name || 'Ticket';

  return (
    <Modal 
      show={show} 
      onHide={handleClose}
      size="xl"
      backdrop="static"
      keyboard={false}
      className="ticket-booking-modal"
    >
      <Modal.Header closeButton className="modal-header-custom">
        <Modal.Title className="modal-title-custom">
          {currentStep === 'selection' && 'Tickets'}
          {currentStep === 'details' && (
            <div className="d-flex align-items-center">
              <i className="fas fa-arrow-left me-3" style={{cursor: 'pointer', color: '#666'}} onClick={() => setCurrentStep('selection')}></i>
              Checkout
            </div>
          )}
          {currentStep === 'payment' && 'Payment'}
          {currentStep === 'thankyou' && 'Booking Confirmed'}
        </Modal.Title>
      </Modal.Header>
      
      <Modal.Body className="modal-body-custom">
        {currentStep === 'selection' && (
          <div className="ticket-selection-step">
            <Row>
              {/* Left Side - Ticket Image and Info */}
              <Col md={5}>
                <div className="ticket-info">
                  {ticket.image_url && (
                    <div className="ticket-image-container mb-3">
                      <img 
                        src={ticket.image_url} 
                        alt={ticketName}
                        className="img-fluid rounded"
                        style={{ width: '100%', height: '180px', objectFit: 'cover' }}
                      />
                    </div>
                  )}
                  <div className="ticket-content">
                    <h4 className="ticket-title mb-2">{ticketName}</h4>
                    {ticket.description && (
                      <p className="ticket-description text-muted small mb-3">{ticket.description}</p>
                    )}
                    
                    {/* Malaysian/Non-Malaysian Tabs */}
                    {ticket.pricing && (
                      <div className="pricing-tabs mb-3">
                        <div className="btn-group w-100" role="group">
                          <button
                            type="button"
                            className={`btn ${activeTab === 'malaysian' ? 'btn-primary' : 'btn-outline-primary'}`}
                            onClick={() => setActiveTab('malaysian')}
                          >
                            Malaysian
                          </button>
                          <button
                            type="button"
                            className={`btn ${activeTab === 'non_malaysian' ? 'btn-primary' : 'btn-outline-primary'}`}
                            onClick={() => setActiveTab('non_malaysian')}
                          >
                            Non-Malaysian
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                  
                  {/* Country Selection */}
                  {ticket.countries && ticket.countries.length > 1 && (
                    <div className="mb-4">
                      <h6 className="mb-3">Select Country/Region</h6>
                      <Form.Select
                        value={selectedCountry?.id || ''}
                        onChange={(e) => {
                          const country = ticket.countries?.find(c => c.id === parseInt(e.target.value));
                          setSelectedCountry(country);
                        }}
                        className="country-select"
                      >
                        {ticket.countries.map((country) => (
                          <option key={country.id} value={country.id}>
                            {country.name} ({country.currency_symbol})
                          </option>
                        ))}
                      </Form.Select>
                    </div>
                  )}
                  
                  {/* Adult Quantity */}
                  <div className="quantity-section mb-3">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <span className="fw-bold">Adult</span>
                        <div className="small text-muted">
                          from {getPrice('adult').currency}{getPrice('adult').price.toFixed(0)}
                        </div>
                      </div>
                      <div className="quantity-controls d-flex align-items-center">
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('adult', -1)}
                          disabled={getCurrentQuantities().adult === 0}
                        >
                          −
                        </Button>
                        <span className="quantity-display mx-3">{getCurrentQuantities().adult}</span>
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('adult', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>

                  {/* Teenagers From 13 Quantity */}
                  <div className="quantity-section mb-3">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <span className="fw-bold">Teenagers From 13</span>
                        <div className="small text-muted">
                          from {getPrice('adult').currency}{getPrice('adult').price.toFixed(0)}
                        </div>
                      </div>
                      <div className="quantity-controls d-flex align-items-center">
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('teenager', -1)}
                          disabled={getCurrentQuantities().teenager === 0}
                        >
                          −
                        </Button>
                        <span className="quantity-display mx-3">{getCurrentQuantities().teenager}</span>
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('teenager', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>

                  {/* University Students Quantity */}
                  <div className="quantity-section mb-3">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <span className="fw-bold">University Students</span>
                        <div className="small text-muted">
                          from {getPrice('adult').currency}{getPrice('adult').price.toFixed(0)}
                        </div>
                      </div>
                      <div className="quantity-controls d-flex align-items-center">
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('university', -1)}
                          disabled={getCurrentQuantities().university === 0}
                        >
                          −
                        </Button>
                        <span className="quantity-display mx-3">{getCurrentQuantities().university}</span>
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('university', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>

                  {/* Children Below 13 Quantity */}
                  <div className="quantity-section mb-4">
                    <div className="d-flex align-items-center justify-content-between">
                      <div>
                        <span className="fw-bold">Children Below 13</span>
                        <div className="small text-muted">
                          from {getPrice('child').currency}{getPrice('child').price.toFixed(0)}
                        </div>
                      </div>
                      <div className="quantity-controls d-flex align-items-center">
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('child', -1)}
                          disabled={getCurrentQuantities().child === 0}
                        >
                          −
                        </Button>
                        <span className="quantity-display mx-3">{getCurrentQuantities().child}</span>
                        <Button 
                          className="quantity-btn"
                          onClick={() => handleQuantityChange('child', 1)}
                        >
                          +
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </Col>

              {/* Right Side - Date and Time Selection */}
              <Col md={7}>
                <div className="booking-options">
                  {/* Date Selection */}
                  <div className="mb-4">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                      <h6 className="mb-0">Select Date</h6>
                      <div className="total-price-display">
                        <strong>{getCurrencySymbol()}{calculateTotal()}</strong>
                        <div className="text-muted small">{getTotalQuantity()} ticket(s)</div>
                      </div>
                    </div>
                    <div className="date-options d-flex gap-2">
                      {[0, 1].map((dayOffset) => {
                        const date = addDays(new Date(), dayOffset);
                        const isSelected = format(selectedDate, 'yyyy-MM-dd') === format(date, 'yyyy-MM-dd');
                        const isToday = dayOffset === 0;
                        
                        return (
                          <button
                            key={dayOffset}
                            className={`date-option ${isSelected ? 'selected' : ''}`}
                            onClick={() => {
                              setSelectedDate(date);
                              setShowCalendar(false);
                            }}
                            type="button"
                          >
                            <div className="date-number">{format(date, 'd')}</div>
                            <div className="date-label">
                              {isToday ? 'Today' : 'Tomorrow'}
                            </div>
                            <div className="date-price" style={{display: 'none'}}>
                              {selectedCountry?.currency_symbol || '$'}{selectedCountry ? parseFloat(selectedCountry.adult_price).toFixed(0) : '49'}
                            </div>
                          </button>
                        );
                      })}
                      
                      {/* Other Dates - Calendar Trigger or Selected Custom Date */}
                      {(() => {
                        const today = new Date();
                        const tomorrow = addDays(new Date(), 1);
                        const isCustomDate = format(selectedDate, 'yyyy-MM-dd') !== format(today, 'yyyy-MM-dd') && 
                                           format(selectedDate, 'yyyy-MM-dd') !== format(tomorrow, 'yyyy-MM-dd');
                        
                        return (
                          <button
                            className={`date-option other-dates ${isCustomDate ? 'selected' : ''}`}
                            onClick={() => setShowCalendar(!showCalendar)}
                            type="button"
                          >
                            {isCustomDate ? (
                              <>
                                <div className="date-number">{format(selectedDate, 'd')}</div>
                                <div className="date-label">{format(selectedDate, 'MMM')}</div>
                              </>
                            ) : (
                              <>
                                <div className="date-icon">📅</div>
                                <div className="date-label">Other Dates</div>
                              </>
                            )}
                            <div className="date-price" style={{display: 'none'}}>
                              {selectedCountry?.currency_symbol || '$'}{selectedCountry ? parseFloat(selectedCountry.adult_price).toFixed(0) : '49'}
                            </div>
                          </button>
                        );
                      })()}
                    </div>

                    {/* Inline Calendar */}
                    {showCalendar && (
                      <div className="inline-calendar-container mt-3">
                        <div className="calendar-header d-flex justify-content-between align-items-center mb-3">
                          <Button variant="link" className="p-0 text-muted">
                            <i className="fas fa-chevron-left"></i>
                          </Button>
                          <h6 className="mb-0 fw-bold">{format(new Date(), 'MMMM yyyy')}</h6>
                          <Button variant="link" className="p-0 text-muted">
                            <i className="fas fa-chevron-right"></i>
                          </Button>
                        </div>
                        
                        <div className="calendar-grid">
                          <div className="calendar-weekdays mb-2">
                            {['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'].map(day => (
                              <div key={day} className="weekday text-muted text-center small">{day}</div>
                            ))}
                          </div>
                          
                          <div className="calendar-days">
                            {(() => {
                              const currentDate = new Date();
                              const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();
                              const today = currentDate.getDate();
                              const tomorrow = today + 1;
                              
                              return Array.from({length: daysInMonth}, (_, i) => i + 1).map(day => {
                                const isToday = day === today;
                                const isTomorrow = day === tomorrow;
                                const isSelected = format(selectedDate, 'yyyy-MM-dd') === format(new Date(currentDate.getFullYear(), currentDate.getMonth(), day), 'yyyy-MM-dd');
                                
                                return (
                                  <div key={day} className={`calendar-day ${isToday || isTomorrow ? 'highlight' : ''} ${isSelected ? 'selected' : ''}`}
                                    onClick={() => {
                                      const newDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                                      setSelectedDate(newDate);
                                      setShowCalendar(false);
                                    }}
                                  >
                                    <div className="day-number">{day}</div>
                                    <div className="day-price small" style={{display: 'none'}}>{getCurrencySymbol()}{getMinPrice()}</div>
                                  </div>
                                );
                              });
                            })()}
                          </div>
                        </div>

                        {/* Time Slots in Calendar */}
                        <div className="calendar-time-slots mt-3 pt-3 border-top">
                          <div className="row g-2">
                            <div className="col-6">
                              <Button 
                                variant="outline-secondary" 
                                size="sm"
                                className="w-100 d-flex justify-content-between"
                                onClick={() => {
                                  setSelectedTime('15:00');
                                  setShowCalendar(false);
                                }}
                              >
                                <span>15:00</span>
                                <span className="text-muted" style={{display: 'none'}}>{getCurrencySymbol()}{getMinPrice()}</span>
                              </Button>
                            </div>
                            <div className="col-6">
                              <Button 
                                variant="outline-secondary" 
                                size="sm"
                                className="w-100 d-flex justify-content-between"
                                onClick={() => {
                                  setSelectedTime('15:30');
                                  setShowCalendar(false);
                                }}
                              >
                                <span>15:30</span>
                                <span className="text-muted" style={{display: 'none'}}>{getCurrencySymbol()}{getMinPrice()}</span>
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    )}
                  </div>

                  {/* Currency Selection */}
                  <div className="mb-4">
                    <h6 className="mb-3">Select Currency</h6>
                    <Form.Select 
                      value={selectedCurrency?.currency_code || ''}
                      onChange={(e) => {
                        const currency = currencies.find(curr => curr.currency_code === e.target.value);
                        setSelectedCurrency(currency);
                      }}
                      className="currency-select"
                    >
                      <option value="">Choose currency...</option>
                      {currencies.map((currency) => (
                        <option key={currency.currency_code} value={currency.currency_code}>
                          {currency.currency_symbol} - {currency.currency_code} ({currency.country_name})
                        </option>
                      ))}
                    </Form.Select>
                  </div>

                  {/* Time Selection */}
                  <div>
                    <h6 className="mb-3">Select time</h6>
                    <div className="time-slots">
                      {['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30'].map((time, index) => {
                        const bestPriceSlots = ['09:00', '09:30', '10:00', '10:30'];
                        const isBestPrice = bestPriceSlots.includes(time);
                        const priceAdjustment = isBestPrice ? 0 : 5;
                        const basePrice = selectedCountry ? parseFloat(selectedCountry.adult_price) : 44;
                        const price = basePrice + priceAdjustment;
                        
                        return (
                          <button
                            key={time}
                            className={`time-slot ${selectedTime === time ? 'selected' : ''} ${isBestPrice ? 'best-price' : ''}`}
                            onClick={() => setSelectedTime(time)}
                            type="button"
                          >
                            <div className="time-label">{time}</div>
                            <div className="time-price" style={{display: 'none'}}>
                              {selectedCountry?.currency_symbol || '$'}{price}
                            </div>
                            {isBestPrice && <div className="best-price-badge">Best price</div>}
                          </button>
                        );
                      })}
                    </div>
                  </div>
                </div>
              </Col>
            </Row>
          </div>
        )}

        {currentStep === 'details' && (
          <div className="checkout-step">
            <Row>
              {/* Left Side - Contact Details */}
              <Col md={6}>
                <div className="contact-details">
                  <h5 className="mb-3">Contact Details</h5>
                  <p className="text-muted mb-4"><span className="text-danger">*</span> Required Fields</p>
                  
                  <Form.Group className="mb-3">
                    <Form.Label>First name <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="text"
                      value={contactForm.firstName}
                      onChange={(e) => setContactForm({...contactForm, firstName: e.target.value})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Last name <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="text"
                      value={contactForm.lastName}
                      onChange={(e) => setContactForm({...contactForm, lastName: e.target.value})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Email <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="email"
                      value={contactForm.email}
                      onChange={(e) => setContactForm({...contactForm, email: e.target.value})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Mobile phone</Form.Label>
                    <Form.Control
                      type="tel"
                      value={contactForm.mobilePhone}
                      onChange={(e) => setContactForm({...contactForm, mobilePhone: e.target.value})}
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Country <span className="text-danger">*</span></Form.Label>
                    <Form.Select
                      value={contactForm.country}
                      onChange={(e) => setContactForm({...contactForm, country: e.target.value})}
                      required
                    >
                      <option value="">Select country</option>
                      <option value="Malaysia">Malaysia</option>
                      <option value="Singapore">Singapore</option>
                      <option value="United States">United States</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="Australia">Australia</option>
                      <option value="India">India</option>
                    </Form.Select>
                  </Form.Group>

                  <Form.Group className="mb-4">
                    <Form.Label>ZIP / Postal Code <span className="text-danger">*</span></Form.Label>
                    <Form.Control
                      type="text"
                      value={contactForm.postalCode}
                      onChange={(e) => setContactForm({...contactForm, postalCode: e.target.value})}
                      required
                    />
                  </Form.Group>

                  {/* Terms and Newsletter */}
                  <Form.Group className="mb-3">
                    <Form.Check
                      type="checkbox"
                      id="terms-checkbox"
                      label={
                        <span>
                          I agree to the <button type="button" className="btn btn-link p-0 text-primary" style={{textDecoration: 'underline', fontSize: 'inherit'}}>booking terms</button> needed to complete the order <span className="text-danger">*</span>
                        </span>
                      }
                      checked={contactForm.receiveUpdates}
                      onChange={(e) => setContactForm({...contactForm, receiveUpdates: e.target.checked})}
                      required
                    />
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Check
                      type="checkbox"
                      id="newsletter-checkbox"
                      label="I would like Kuantan 188 to send me exclusive updates and the latest offers."
                      checked={contactForm.receiveUpdates}
                      onChange={(e) => setContactForm({...contactForm, receiveUpdates: e.target.checked})}
                    />
                  </Form.Group>
                </div>
              </Col>

              {/* Right Side - Order Summary */}
              <Col md={6}>
                <div className="order-summary-card">
                  <div className="ticket-card mb-4">
                    {ticket.image_url && (
                      <img 
                        src={ticket.image_url} 
                        alt={ticketName}
                        className="ticket-image"
                      />
                    )}
                    <div className="ticket-info">
                      <h6 className="ticket-title">360 CHICAGO {ticketName}</h6>
                      <p className="ticket-subtitle">{ticketName}</p>
                      <p className="ticket-datetime">{format(selectedDate, 'd MMMM yyyy HH:mm')}</p>
                      
                      <div className="ticket-quantity">
                        {getCurrentQuantities().adult > 0 && (
                          <div className="d-flex justify-content-between align-items-center">
                            <span className="text-success">{getCurrentQuantities().adult}× Adult</span>
                            <span>{getCurrencySymbol()}{(getPrice('adult').price * getCurrentQuantities().adult).toFixed(0)}</span>
                          </div>
                        )}
                        {getCurrentQuantities().child > 0 && (
                          <div className="d-flex justify-content-between align-items-center mt-1">
                            <span className="text-success">{getCurrentQuantities().child}× Child</span>
                            <span>{getCurrencySymbol()}{(getPrice('child').price * getCurrentQuantities().child).toFixed(0)}</span>
                          </div>
                        )}
                      </div>
                      
                      <div className="ticket-actions mt-3">
                        <Button variant="link" className="p-0 text-success me-3" size="sm">Edit</Button>
                        <Button variant="link" className="p-0 text-success" size="sm">Remove</Button>
                      </div>
                    </div>
                  </div>

                  <Button className="continue-shopping-btn w-100 mb-4" variant="success" size="sm">
                    + Continue shopping
                  </Button>

                  {/* Pricing Breakdown */}
                  <div className="pricing-breakdown">
                    <div className="d-flex justify-content-between mb-2">
                      <span>Subtotal</span>
                      <span>{selectedCountry?.currency_symbol || '$'}{calculateTotal().toFixed(2)}</span>
                    </div>
                    <div className="d-flex justify-content-between mb-2">
                      <span>Amusement Tax</span>
                      <span>{selectedCountry?.currency_symbol || '$'}{(calculateTotal() * 0.05).toFixed(2)}</span>
                    </div>
                    <div className="d-flex justify-content-between mb-3">
                      <span>Bar Tax</span>
                      <span>{selectedCountry?.currency_symbol || '$'}{(calculateTotal() * 0.03).toFixed(2)}</span>
                    </div>
                    
                    <hr />
                    
                    <div className="d-flex justify-content-between total-due">
                      <strong>Total Due</strong>
                      <strong>{selectedCountry?.currency_symbol || '$'}{(calculateTotal() * 1.08).toFixed(2)}</strong>
                    </div>

                    <div className="promo-section mt-3">
                      <Button variant="link" className="p-0 text-success">
                        <i className="fas fa-plus-circle me-2"></i>
                        Enter promo / gift card code
                      </Button>
                    </div>
                  </div>
                </div>
              </Col>
            </Row>
          </div>
        )}

        {currentStep === 'payment' && (
          <div className="payment-step">
            <Row>
              {/* Left Column - Payment Method */}
              <Col md={7}>
                <div className="ms-4">
                  <PaymentMethodSelector
                    selectedMethod={paymentMethod}
                    onMethodChange={setPaymentMethod}
                    totalAmount={calculateTotal()}
                    currency={selectedCountry?.currency_symbol || 'RM'}
                  />
                </div>
              </Col>

              {/* Right Column - Order Summary */}
              <Col md={5}>
                <div className="payment-summary p-4 bg-white border rounded me-4">
                  <h6 className="mb-3">Order Summary</h6>
                  <div className="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>{selectedCountry?.currency_symbol || 'RM'}{calculateTotal().toFixed(2)}</span>
                  </div>
                  <div className="d-flex justify-content-between mb-2">
                    <span>Delivery Fee:</span>
                    <span className="text-success">FREE</span>
                  </div>
                  <hr />
                  <div className="d-flex justify-content-between">
                    <strong>Total Amount:</strong>
                    <strong className="text-primary">{selectedCountry?.currency_symbol || 'RM'}{calculateTotal().toFixed(2)}</strong>
                  </div>
                  
                  <div className="mt-3 p-3 bg-light border rounded">
                    <div className="d-flex align-items-center">
                      <span className="fw-bold">Payment Method:</span>
                      <span className="ms-2 badge bg-primary">
                        {paymentMethod === 'cash_on_delivery' ? 'Cash on Delivery' : 'Billplz Online Payment'}
                      </span>
                    </div>
                  </div>
                </div>
              </Col>
            </Row>
          </div>
        )}

        {currentStep === 'thankyou' && (
          <div className="thankyou-step text-center">
            <div className="success-icon mb-3">
              <i className="fas fa-check-circle text-success" style={{ fontSize: '4rem' }}></i>
            </div>
            <h4 className="text-success mb-3">Booking Confirmed!</h4>
            <p>Thank you for your booking. 
              {paymentMethod === 'billplz' ? (
                <>Your payment page has opened in a new tab. Please complete the payment to secure your booking.</>
              ) : (
                <>Your ticket confirmation has been sent to {contactForm.email}</>
              )}
            </p>
            
            {bookingResult && (
              <div className="alert alert-success mt-3">
                <strong>Booking Reference:</strong> {bookingResult.booking_reference}
              </div>
            )}
            
            <div className="booking-details mt-4 p-3 bg-light rounded">
              <h6>Booking Details:</h6>
              <p><strong>Ticket:</strong> {ticketName}</p>
              <p><strong>Date & Time:</strong> {format(selectedDate, 'MMMM d, yyyy')} at {selectedTime}</p>
              <div className="mb-2">
                <strong>Tickets:</strong>
                {getCurrentQuantities().adult > 0 && <div className="ms-2">• Adult × {getCurrentQuantities().adult}</div>}
                {getCurrentQuantities().child > 0 && <div className="ms-2">• Child × {getCurrentQuantities().child}</div>}
              </div>
              <p><strong>Total Amount:</strong> {selectedCountry?.currency_symbol}{calculateTotal().toFixed(2)}</p>
              <p><strong>Payment Method:</strong> {paymentMethod === 'cash_on_delivery' ? 'Cash on Delivery' : 'Billplz Online Payment'}</p>
            </div>
          </div>
        )}
      </Modal.Body>
      
      <Modal.Footer className="modal-footer-custom">
        {currentStep === 'selection' && (
          <>
            <div className="total-summary d-flex justify-content-between align-items-center w-100 mb-3">
              <div>
                <div className="total-label">Total for {getTotalQuantity()} ticket(s)</div>
                <div className="total-breakdown text-muted small">
                  {getCurrentQuantities().adult > 0 && `${getCurrentQuantities().adult} Adult${getCurrentQuantities().adult > 1 ? 's' : ''}`}
                  {getCurrentQuantities().adult > 0 && getCurrentQuantities().child > 0 && ', '}
                  {getCurrentQuantities().child > 0 && `${getCurrentQuantities().child} Child${getCurrentQuantities().child > 1 ? 'ren' : ''}`}
                </div>
              </div>
              <div className="total-price">
                <strong style={{fontSize: '1.5rem'}}>{getCurrencySymbol()}{calculateTotal()}</strong>
              </div>
            </div>
            <Button 
              className="continue-button w-100"
              onClick={handleContinueToDetails}
              disabled={getTotalQuantity() === 0 || !selectedTime}
            >
              Continue →
            </Button>
          </>
        )}
        
        {currentStep === 'details' && (
          <>
            <Button variant="secondary" onClick={() => setCurrentStep('selection')}>
              Back
            </Button>
            <Button variant="primary" onClick={handleContinueToPayment}>
              Continue to Payment
            </Button>
          </>
        )}
        
        {currentStep === 'payment' && (
          <>
            <Button variant="secondary" onClick={() => setCurrentStep('details')}>
              Back
            </Button>
            <Button variant="primary" onClick={handlePayment}>
              Process Payment
            </Button>
          </>
        )}
        
        {currentStep === 'thankyou' && (
          <Button variant="primary" onClick={handleClose} className="mx-auto">
            Close
          </Button>
        )}
      </Modal.Footer>
      
      {/* Payment Loading Modal */}
      <PaymentLoading 
        show={isProcessingPayment}
        title={paymentMethod === 'billplz' ? "Creating Payment Link" : "Processing Booking"}
        message={paymentMethod === 'billplz' 
          ? "Please wait while we create your secure payment link..."
          : "Please wait while we process your booking..."
        }
      />

    </Modal>
  );
};

export default TicketBookingModal;