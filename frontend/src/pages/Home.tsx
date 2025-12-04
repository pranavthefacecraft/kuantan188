import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Container, Row, Col, Button } from 'react-bootstrap';
import { eventsApi, Event } from '../services/api';
import ReservationModal from '../components/modals/ReservationModal';
import TicketBookingModal from '../components/modals/TicketBookingModal';
import { ReviewsWidget } from '../components/GoogleReviews';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/free-mode';

const Home: React.FC = () => {
  const navigate = useNavigate();

  const [showModal, setShowModal] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState<Event | null>(null);
  
  // Ticket booking modal state
  const [showTicketModal, setShowTicketModal] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState<any>(null);


  // Tickets from API
  const [tickets, setTickets] = useState<any[]>([]);

  // Book Now events from API
  const [bookNowEvents, setBookNowEvents] = useState<Event[]>([]);


  useEffect(() => {
    const fetchData = async () => {
      try {
        // Fetch tickets and events
        const [ticketsResponse, bookNowResponse] = await Promise.all([
          eventsApi.getTickets(),
          eventsApi.getBookNowEvents()
        ]);
        
        if (ticketsResponse.success) {
          setTickets(ticketsResponse.data);
        } else {
          console.error('Failed to load tickets');
        }
        
        if (bookNowResponse.success) {
          setBookNowEvents(bookNowResponse.data);
        } else {
          console.error('Failed to load Book Now events');
        }
        
      } catch (err) {
        console.error('Error fetching data:', err);
      }
    };

    fetchData();
  }, []);





  const handleReserve = (event: Event) => {
    setSelectedEvent(event);
    setShowModal(true);
  };

  const handleGetTickets = (ticket: any) => {
    setSelectedTicket(ticket);
    setShowTicketModal(true);
  };

  const handleCloseTicketModal = () => {
    setShowTicketModal(false);
    setSelectedTicket(null);
  };

  const handleViewDetails = (eventId: number) => {
    navigate(`/events/${eventId}`);
  };

  // Debug Swiper initialization
  useEffect(() => {
    console.log('🔍 Swiper Debug - Component mounted');
    console.log('📊 Tickets count:', tickets.length, tickets);
    console.log('📊 Book Now events count:', bookNowEvents.length, bookNowEvents);
    
    // Check if Swiper elements exist
    setTimeout(() => {
      console.log('🕒 Checking DOM elements after 2 seconds...');
      
      const ticketsContainer = document.querySelector('.tickets-swiper');
      const eventsContainer = document.querySelector('.events-swiper');
      const ticketsSlides = document.querySelectorAll('.tickets-swiper .swiper-slide');
      const eventsSlides = document.querySelectorAll('.events-swiper .swiper-slide');
      const ticketsPrevButton = document.querySelector('.swiper-button-prev-tickets');
      const ticketsNextButton = document.querySelector('.swiper-button-next-tickets');
      const eventsPrevButton = document.querySelector('.swiper-button-prev-events');
      const eventsNextButton = document.querySelector('.swiper-button-next-events');

      console.log('🎯 Tickets container found:', !!ticketsContainer);
      console.log('🎯 Events container found:', !!eventsContainer);
      console.log('📋 Tickets slides count:', ticketsSlides.length);
      console.log('📋 Events slides count:', eventsSlides.length);
      console.log('⬅️ Tickets prev button found:', !!ticketsPrevButton);
      console.log('➡️ Tickets next button found:', !!ticketsNextButton);
      console.log('⬅️ Events prev button found:', !!eventsPrevButton);
      console.log('➡️ Events next button found:', !!eventsNextButton);
      
      // Also check if sections are rendering
      const heroSections = document.querySelectorAll('.hero-section');
      console.log('🏛️ Hero sections found:', heroSections.length);
    }, 2000);
  }, [tickets, bookNowEvents]);







  return (
    <div>
      {/* Top Section - Get Your Tickets */}
      <section className="bg-custom-dark position-relative overflow-hidden hero-section d-flex align-items-center">
        <div className="hero-container position-relative d-flex align-items-center w-100 mx-auto mt-300" style={{ zIndex: 1 }}>
          {/* Left Side - Get Your Tickets Section */}
          <div className="hero-book-section flex-shrink-0">
            <div>
            <div className="text-start">
              <h1 className="hero-title-main">
                <span className="hero-title-white">Get Your</span><br />
                <span className="hero-title-red">Tickets</span>
              </h1>
              <p className="hero-subtitle">
                Choose your experience,<br />
                book your entry today!
              </p>
            </div>
              </div>
          </div>
          
          {/* Right Side - Tickets Slider */}
          <div className="hero-slider-section flex-grow-1">
          <div className="position-relative">
            {/* Swiper Event Cards Container */}
            <Swiper
              modules={[Navigation, FreeMode]}
              spaceBetween={16}
              slidesPerView="auto"
              freeMode={true}
              loop={false}
              centeredSlides={false}
              navigation={{
                prevEl: '.swiper-button-prev-tickets',
                nextEl: '.swiper-button-next-tickets',
              }}
              grabCursor={true}
              allowTouchMove={true}
              simulateTouch={true}
              className="tickets-swiper"
              onInit={(swiper: any) => {
                console.log('🚀 TICKETS Swiper initialized successfully');
                console.log('📊 TICKETS Swiper params:', swiper.params);
                console.log('🎯 TICKETS slides count:', swiper.slides.length);
                console.log('🔄 TICKETS FreeMode enabled:', swiper.params.freeMode);
                console.log('🎯 TICKETS Navigation enabled:', !!swiper.navigation);
                console.log('📐 TICKETS Container width:', swiper.width);
                console.log('📏 TICKETS Slide width:', swiper.slidesSizesGrid);
                // Force slide width to 350px
                swiper.slides.forEach((slide: any) => {
                  slide.style.width = '350px';
                  slide.style.minWidth = '350px';
                  slide.style.maxWidth = '350px';
                });
                swiper.update();
              }}
              onSlideChange={(swiper: any) => {
                console.log('🔄 TICKETS Slide changed to:', swiper.activeIndex);
              }}
              onTouchMove={(swiper: any) => {
                console.log('👆 TICKETS Touch move - translate:', swiper.translate);
              }}
              onTouchStart={() => {
                console.log('🤚 TICKETS Touch start detected');
              }}
              onTouchEnd={() => {
                console.log('✋ TICKETS Touch end detected');
              }}
              breakpoints={{
                320: {
                  slidesPerView: 1,
                },
                768: {
                  slidesPerView: 2,
                },
                1024: {
                  slidesPerView: 3,
                },
              }}
            >
              {tickets.length > 0 ? tickets.map((ticket, ticketIndex) => (
                <SwiperSlide key={ticket.id || ticketIndex} className="event-card-item">
                  <div 
                    className="event-card-background h-100 d-flex flex-column"
                    style={{
                      backgroundImage: ticket.image_url 
                        ? `url(${ticket.image_url})` 
                        : `linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)`,
                      backgroundColor: '#1A0007' // Fallback color
                    }}
                  >
                    <div className="event-card-overlay"></div>
                    <div className="event-card-content p-4 d-flex flex-column h-100">
                      <h3 className="column-title mb-2">{ticket.title || ticket.name}</h3>
                      <div className="mt-auto">
                        <p className="pricing-label mb-1">Starting at</p>
                        <p className="pricing-amount mb-3">
                          {ticket.adult_price ? `RM ${ticket.adult_price}` : 
                           ticket.price ? `RM ${ticket.price}` : 'RM 20.00'}
                        </p>
                        <Button 
                          className="reserve-button get-tickets-button"
                          onClick={() => handleGetTickets(ticket)}
                        >
                          Get Tickets
                        </Button>
                      </div>
                    </div>
                  </div>
                </SwiperSlide>
              )) : (
                // Fallback when no tickets are loaded
                [1,2,3].map((index) => (
                  <SwiperSlide key={index} className="event-card-item">
                    <div 
                      className="event-card-background h-100 d-flex flex-column"
                      style={{
                        background: 'linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)',
                        backgroundColor: '#1A0007'
                      }}
                    >
                      <div className="event-card-overlay"></div>
                      <div className="event-card-content p-4 d-flex flex-column h-100">
                        <h3 className="column-title mb-2">Loading...</h3>
                        <div className="mt-auto">
                          <p className="pricing-label mb-1">Starting at</p>
                          <p className="pricing-amount mb-3">RM --</p>
                          <Button className="reserve-button get-tickets-button" disabled>
                            Get Tickets
                          </Button>
                        </div>
                      </div>
                    </div>
                  </SwiperSlide>
                ))
              )}
            </Swiper>

            {/* Custom Navigation Arrows for Tickets */}
            <button 
              className="swiper-button-prev-tickets slider-nav-btn slider-nav-left btn btn-outline-light position-absolute top-50 start-0 translate-middle-y"
              onClick={() => console.log('🔥 TICKETS LEFT ARROW CLICKED')}
            >
              <i className="fas fa-chevron-left"></i>
            </button>
            <button 
              className="swiper-button-next-tickets slider-nav-btn slider-nav-right btn btn-outline-light position-absolute top-50 end-0 translate-middle-y"
              onClick={() => console.log('🔥 TICKETS RIGHT ARROW CLICKED')}
            >
              <i className="fas fa-chevron-right"></i>
            </button>
          </div>
          </div>
        </div>
      </section>

      {/* Bottom Section - Book Now Events */}
      <section className="bg-custom-dark position-relative overflow-hidden hero-section d-flex align-items-center">
        <div className="hero-container position-relative d-flex align-items-center w-100 mx-auto" style={{ zIndex: 1 }}>
          {/* Left Side - Book Now Section */}
          <div className="hero-book-section flex-shrink-0">
            <div>
            <div className="text-start">
              <h1 className="hero-title-main">
                <span className="hero-title-white">Book</span><br />
                <span className="hero-title-red">Now</span>
              </h1>
              <p className="hero-subtitle">
                Select the perfect event package<br />
                to unlock your ultimate experience
              </p>
            </div>
              </div>
          </div>
          
          {/* Right Side - Events Slider */}
          <div className="hero-slider-section flex-grow-1">
          <div className="position-relative">
            {/* Swiper Events Container */}
            <Swiper
              modules={[Navigation, FreeMode]}
              spaceBetween={16}
              slidesPerView="auto"
              freeMode={true}
              loop={false}
              centeredSlides={false}
              navigation={{
                prevEl: '.swiper-button-prev-events',
                nextEl: '.swiper-button-next-events',
              }}
              grabCursor={true}
              allowTouchMove={true}
              simulateTouch={true}
              className="events-swiper"
              onInit={(swiper: any) => {
                console.log('🚀 EVENTS Swiper initialized successfully');
                console.log('📊 EVENTS Swiper params:', swiper.params);
                console.log('🎯 EVENTS slides count:', swiper.slides.length);
                console.log('🔄 EVENTS FreeMode enabled:', swiper.params.freeMode);
                console.log('🎯 EVENTS Navigation enabled:', !!swiper.navigation);
                console.log('📐 EVENTS Container width:', swiper.width);
                console.log('📏 EVENTS Slide width:', swiper.slidesSizesGrid);
                // Force slide width to 350px
                swiper.slides.forEach((slide: any) => {
                  slide.style.width = '350px';
                  slide.style.minWidth = '350px';
                  slide.style.maxWidth = '350px';
                });
                swiper.update();
              }}
              onSlideChange={(swiper: any) => {
                console.log('🔄 EVENTS Slide changed to:', swiper.activeIndex);
              }}
              onTouchMove={(swiper: any) => {
                console.log('👆 EVENTS Touch move - translate:', swiper.translate);
              }}
              onTouchStart={() => {
                console.log('🤚 EVENTS Touch start detected');
              }}
              onTouchEnd={() => {
                console.log('✋ EVENTS Touch end detected');
              }}
              breakpoints={{
                320: {
                  slidesPerView: 1,
                },
                768: {
                  slidesPerView: 2,
                },
                1024: {
                  slidesPerView: 3,
                },
              }}
            >
              {bookNowEvents.length > 0 ? (
                bookNowEvents.map((event, eventIndex) => (
                  <SwiperSlide key={event.id} className="event-card-item">
                    <div 
                      className="event-card-background h-100 d-flex flex-column"
                      style={{
                        backgroundImage: event.image_url 
                          ? `url(${event.image_url})` 
                          : `linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)`,
                        backgroundColor: '#1A0007' // Fallback color
                      }}
                      onError={(e) => {
                        // Fallback if image fails to load
                        const target = e.target as HTMLElement;
                        target.style.backgroundImage = 'linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)';
                      }}
                    >
                      <div className="event-card-overlay"></div>
                      <div className="event-card-content p-4 d-flex flex-column h-100">
                        <h3 className="column-title mb-2">{event.title}</h3>
                        <p className="event-description mb-3 flex-grow-1">
                          {event.description}
                        </p>
                        <div className="mt-auto">
                          <p className="pricing-label mb-1">Starting at</p>
                          <p className="pricing-amount mb-3">{event.price_display}</p>
                          <div className="d-flex gap-2 flex-column">
                            <Button 
                              variant="outline-light"
                              size="sm"
                              onClick={() => handleViewDetails(event.id)}
                            >
                              View Details
                            </Button>
                            <Button 
                              className="reserve-button get-tickets-button"
                              onClick={() => handleReserve(event)}
                            >
                              Reserve
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </SwiperSlide>
                ))
              ) : (
                <SwiperSlide className="event-card-item">
                  <div className="event-card-background h-100 d-flex flex-column align-items-center justify-content-center" style={{ backgroundColor: '#1A0007' }}>
                    <div className="text-center">
                      <h5 className="text-white">No Book Now events available</h5>
                      <p className="text-white">Check back soon for exciting events!</p>
                    </div>
                  </div>
                </SwiperSlide>
              )}
            </Swiper>

            {/* Custom Navigation Arrows for Events */}
            <button 
              className="swiper-button-prev-events slider-nav-btn slider-nav-left btn btn-outline-light position-absolute top-50 start-0 translate-middle-y"
              onClick={() => console.log('🔥 EVENTS LEFT ARROW CLICKED')}
            >
              <i className="fas fa-chevron-left"></i>
            </button>
            <button 
              className="swiper-button-next-events slider-nav-btn slider-nav-right btn btn-outline-light position-absolute top-50 end-0 translate-middle-y"
              onClick={() => console.log('🔥 EVENTS RIGHT ARROW CLICKED')}
            >
              <i className="fas fa-chevron-right"></i>
            </button>
          </div>
          </div>
        </div>
      </section>

      {/* Customer Reviews Section */}
      <section className="py-5" style={{ backgroundColor: '#1A0007' }}>
        <Container>
          <Row className="mb-5">
            <Col>
              <h2 className="text-center fw-bold mb-3 text-white" style={{ fontFamily: 'Erstoria', fontSize: '72px' }}>
                Our guests feedback
              </h2>
            </Col>
          </Row>
          <ReviewsWidget 
            limit={3} 
            showViewAll={true}
            className="mb-4"
          />
        </Container>
      </section>

      {/* Reservation Modal */}
      <ReservationModal 
        show={showModal}
        onHide={() => setShowModal(false)}
        event={selectedEvent}
      />

      {/* Ticket Booking Modal */}
      <TicketBookingModal
        show={showTicketModal}
        onHide={handleCloseTicketModal}
        ticket={selectedTicket}
      />
    </div>
  );
};

export default Home;