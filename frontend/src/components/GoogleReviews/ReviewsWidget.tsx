import React, { useState, useEffect } from 'react';
import { Card, Spinner } from 'react-bootstrap';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

interface ReviewStats {
  total_reviews: number;
  average_rating: number;
  rating_breakdown: {
    5: number;
    4: number;
    3: number;
    2: number;
    1: number;
  };
}

interface GoogleReview {
  id: number;
  author_name: string;
  rating: number;
  text?: string;
  review_time: string;
}

interface ReviewsWidgetProps {
  limit?: number;
  showViewAll?: boolean;
  className?: string;
}

const ReviewsWidget: React.FC<ReviewsWidgetProps> = ({
  limit = 3,
  showViewAll = true,
  className = ''
}) => {
  const [reviews, setReviews] = useState<GoogleReview[]>([]);
  const [stats, setStats] = useState<ReviewStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchReviews = async () => {
      try {
        setLoading(true);
        const apiBaseUrl = process.env.REACT_APP_API_URL || (process.env.NODE_ENV === 'production' ? 'https://admin.tfcmockup.com/api' : 'http://127.0.0.1:8000/api');
        const response = await fetch(`${apiBaseUrl}/public/reviews?per_page=50`);
        const data = await response.json();

        if (data.success) {
          setReviews(data.data);
          setStats(data.stats);
        } else {
          setError('Failed to load reviews');
        }
      } catch (err) {
        console.error('Error fetching reviews:', err);
        setError('Failed to load reviews');
      } finally {
        setLoading(false);
      }
    };

    fetchReviews();
  }, [limit]);

  // Render star rating
  const renderStars = (rating: number) => {
    const stars = [];
    
    for (let i = 1; i <= 5; i++) {
      stars.push(
        <span key={i} style={{ 
          color: i <= rating ? '#fbbc04' : '#dadce0',
          fontSize: '16px',
          marginRight: '2px'
        }}>
          {i <= rating ? '★' : '☆'}
        </span>
      );
    }
    return <div className="d-flex align-items-center">{stars}</div>;
  };

  // Truncate text
  const truncateText = (text: string, maxLength: number = 120) => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  };



  if (loading) {
    return (
      <div className={`text-center py-4 ${className}`}>
        <Spinner animation="border" size="sm" variant="primary" />
        <p className="mt-2 mb-0 text-muted small">Loading reviews...</p>
      </div>
    );
  }

  if (error || !stats) {
    return (
      <div className={`text-center py-4 ${className}`}>
        <p className="text-muted mb-0">Reviews temporarily unavailable</p>
      </div>
    );
  }

  return (
    <div className={className}>
      {/* Reviews Slider */}
      <div style={{ position: 'relative', padding: '0 60px' }}>
        <Swiper
          modules={[Navigation, Pagination, Autoplay]}
          spaceBetween={30}
          slidesPerView={1}
          navigation={{
            nextEl: '.swiper-button-next-custom',
            prevEl: '.swiper-button-prev-custom',
          }}
          pagination={{ clickable: true }}
          autoplay={{
            delay: 5000,
            disableOnInteraction: false,
          }}
          loop={true}
          breakpoints={{
            640: {
              slidesPerView: 2,
            },
            1024: {
              slidesPerView: 3,
            },
          }}
          style={{
            '--swiper-pagination-color': '#333',
          } as React.CSSProperties}
        >
        {reviews.map((review) => (
          <SwiperSlide key={review.id}>
            <Card className="h-100 shadow-sm" style={{ 
              backgroundColor: '#ffffff',
              border: '1px solid #e3e3e3',
              borderRadius: '12px',
              marginBottom: '40px'
            }}>
              <Card.Body className="p-4">
                {/* Header with name and Google logo */}
                <div className="d-flex justify-content-between align-items-start">
                  <h5 className="mb-0" style={{ 
                    color: '#333333',
                    fontSize: '20px',
                    fontFamily: 'Poppins',
                    fontWeight: '700',
                    lineHeight: '33.6px'
                  }}>
                    {review.author_name}
                  </h5>
                  <div className="d-flex align-items-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" style={{ marginTop: '2px' }}>
                      <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                      <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                      <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                      <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                  </div>
                </div>
                
                {/* Star Rating */}
                <div className="mb-3">
                  {renderStars(review.rating)}
                </div>
                
                {/* Review Text */}
                {review.text && (
                  <p className="mb-0" style={{ 
                    color: '#333333',
                    fontFamily: 'Poppins',
                    fontWeight: '400',
                    fontStyle: 'normal',
                    fontSize: '16px',
                    lineHeight: '27px',
                    letterSpacing: '0%',
                    verticalAlign: 'middle'
                  }}>
                    {truncateText(review.text, 150)}
                  </p>
                )}
              </Card.Body>
            </Card>
          </SwiperSlide>
        ))}
        </Swiper>
        
        {/* Custom Navigation Buttons */}
        <div 
          className="swiper-button-prev-custom"
          style={{
            position: 'absolute',
            left: '10px',
            top: '50%',
            transform: 'translateY(-50%)',
            width: '40px',
            height: '40px',
            backgroundColor: 'white',
            borderRadius: '50%',
            boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            cursor: 'pointer',
            zIndex: 10,
            border: '1px solid #e3e3e3'
          }}
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M15 18L9 12L15 6" stroke="#333" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </div>
        
        <div 
          className="swiper-button-next-custom"
          style={{
            position: 'absolute',
            right: '10px',
            top: '50%',
            transform: 'translateY(-50%)',
            width: '40px',
            height: '40px',
            backgroundColor: 'white',
            borderRadius: '50%',
            boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            cursor: 'pointer',
            zIndex: 10,
            border: '1px solid #e3e3e3'
          }}
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M9 18L15 12L9 6" stroke="#333" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </div>
      </div>


    </div>
  );
};

export default ReviewsWidget;