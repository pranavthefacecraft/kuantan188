import React, { useState, useEffect, useMemo } from 'react';
import { Container, Row, Col, Card, Button, Badge, Spinner, Alert } from 'react-bootstrap';
import { eventsApi, Event } from '../services/api';
import ReservationModal from '../components/modals/ReservationModal';
import { ReviewsWidget } from '../components/GoogleReviews';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/free-mode';

const Home: React.FC = () => {
  const [featuredEvents, setFeaturedEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState<Event | null>(null);


  // Top slider - Tickets (Sky Deck, Observation Deck, Sky Walk)
  const ticketTypes = useMemo(() => [
    { name: 'Sky Deck', price: 'RM 20.00', image: '/skydeck.webp' },
    { name: 'Observation Deck', price: 'RM 20.00', image: '/skydeck.webp' },
    { name: 'Sky Walk', price: 'RM 20.00', image: '/skydeck.webp' },
    { name: 'Sky Walk1', price: 'RM 20.00', image: '/skydeck.webp' }
  ], []);

  // Book Now events from API
  const [bookNowEvents, setBookNowEvents] = useState<Event[]>([]);
  const [bookNowLoading, setBookNowLoading] = useState(true);


  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setBookNowLoading(true);
        
        // Fetch both featured events and book now events in parallel
        const [featuredResponse, bookNowResponse] = await Promise.all([
          eventsApi.getFeaturedEvents(),
          eventsApi.getBookNowEvents()
        ]);
        
        if (featuredResponse.success) {
          setFeaturedEvents(featuredResponse.data);
        } else {
          setError('Failed to load featured events');
        }
        
        if (bookNowResponse.success) {
          setBookNowEvents(bookNowResponse.data);
        } else {
          console.error('Failed to load Book Now events');
        }
        
      } catch (err) {
        console.error('Error fetching data:', err);
        setError('Failed to load events. Please try again later.');
      } finally {
        setLoading(false);
        setBookNowLoading(false);
      }
    };

    fetchData();
  }, []);





  const handleReserveNow = (event: Event) => {
    setSelectedEvent(event);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setSelectedEvent(null);
  };

  // Debug Swiper initialization
  useEffect(() => {
    console.log('🔍 Swiper Debug - Component mounted');
    console.log('📊 Ticket types count:', ticketTypes.length, ticketTypes);
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
  }, [ticketTypes, bookNowEvents]);







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
              {ticketTypes.map((ticket, ticketIndex) => (
                <SwiperSlide key={ticketIndex} className="event-card-item">
                  <div 
                    className="event-card-background h-100 d-flex flex-column"
                    style={{
                      backgroundImage: ticket.image 
                        ? `url(${ticket.image})` 
                        : `linear-gradient(135deg, #1A0007 0%, #4A0E15 100%)`,
                      backgroundColor: '#1A0007' // Fallback color
                    }}
                  >
                    <div className="event-card-overlay"></div>
                    <div className="event-card-content p-4 d-flex flex-column h-100">
                      <h3 className="column-title mb-2">{ticket.name}</h3>
                      <div className="mt-auto">
                        <p className="pricing-label mb-1">Starting at</p>
                        <p className="pricing-amount mb-3">{ticket.price}</p>
                        <Button className="reserve-button get-tickets-button">
                          Get Tickets
                        </Button>
                      </div>
                    </div>
                  </div>
                </SwiperSlide>
              ))}
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
              {bookNowLoading ? (
                <SwiperSlide className="event-card-item">
                  <div className="event-card-background h-100 d-flex flex-column align-items-center justify-content-center" style={{ backgroundColor: '#1A0007' }}>
                    <div className="text-center">
                      <Spinner animation="border" variant="light" />
                      <p className="text-white mt-3">Loading events...</p>
                    </div>
                  </div>
                </SwiperSlide>
              ) : bookNowEvents.length > 0 ? (
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
                          <Button 
                            className="reserve-button get-tickets-button"
                            onClick={() => handleReserveNow(event)}
                          >
                            Reserve
                          </Button>
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

      {/* Featured Events Section */}
      <section className="py-5">
        <Container>
          <Row className="mb-4">
            <Col>
              <h2 className="text-center fw-bold mb-4">Featured Events</h2>
              <p className="text-center text-muted">
                Don't miss these amazing upcoming events in Kuantan
              </p>
            </Col>
          </Row>
                <Row>
            {loading ? (
              <Col className="text-center">
                <Spinner animation="border" role="status" variant="primary">
                  <span className="visually-hidden">Loading events...</span>
                </Spinner>
                <p className="mt-2 text-muted">Loading amazing events...</p>
              </Col>
            ) : error ? (
            <Col>
              <Alert variant="warning" className="text-center">
                <Alert.Heading>Oops! Something went wrong</Alert.Heading>
                <p>{error}</p>
                <Button 
                  variant="outline-warning" 
                  onClick={() => window.location.reload()}
                >
                  Try Again
                </Button>
              </Alert>
            </Col>
          ) : (
            <>
              {featuredEvents.map((event) => (
                <Col lg={4} md={6} key={event.id} className="mb-4">
                  <Card className="h-100 border-0 shadow-sm hover-lift">
                    <div className="position-relative">
                      <Card.Img 
                        variant="top" 
                        src={event.image_url} 
                        alt={event.title}
                        style={{ height: '200px', objectFit: 'cover' }}
                        onError={(e) => {
                          const target = e.target as HTMLImageElement;
                          target.src = `https://picsum.photos/400/250?random=${event.id}`;
                        }}
                      />
                      <Badge 
                        bg="primary" 
                        className="position-absolute top-0 end-0 m-3"
                      >
                        {event.category}
                      </Badge>
                    </div>
                    <Card.Body className="d-flex flex-column">
                      <Card.Title className="fw-bold mb-2">{event.title}</Card.Title>
                      <Card.Text className="text-muted mb-3 flex-grow-1">
                        📅 {event.event_date_formatted} at {event.event_time_formatted}<br />
                        📍 {event.location}
                      </Card.Text>
                      <div className="d-flex justify-content-between align-items-center">
                        <span className="fw-bold text-primary">{event.price_display}</span>
                        <Button 
                          variant="outline-primary" 
                          size="sm"
                          onClick={() => handleReserveNow(event)}
                        >
                          Reserve Now
                        </Button>
                      </div>
                    </Card.Body>
                  </Card>
                </Col>
              ))}
              {featuredEvents.length === 0 && !loading && (
                <Col className="text-center">
                  <div className="py-5">
                    <h5 className="text-muted">No events available at the moment</h5>
                    <p className="text-muted">Check back soon for exciting upcoming events!</p>
                  </div>
                </Col>
              )}
            </>
          )}
          </Row>
        </Container>
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
        onHide={handleCloseModal}
        event={selectedEvent}
      />
    </div>
  );
};

export default Home;