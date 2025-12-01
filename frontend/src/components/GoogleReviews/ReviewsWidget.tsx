import React, { useState, useEffect } from 'react';
import { Row, Col, Card, Button, Spinner } from 'react-bootstrap';
import { Star, StarFill, ArrowRight } from 'react-bootstrap-icons';

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
        const response = await fetch(`${apiBaseUrl}/public/reviews?per_page=${limit}&rating=5`);
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
        <span key={i}>
          {i <= rating ? (
            <StarFill className="text-warning" />
          ) : (
            <Star className="text-muted" />
          )}
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

  // Format date
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
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
      {/* Stats Header */}
      <div className="text-center mb-4">
        <div className="d-flex align-items-center justify-content-center mb-2">
          <div className="display-6 fw-bold text-primary me-3">
            {stats.average_rating}
          </div>
          <div>
            {renderStars(Math.round(stats.average_rating))}
            <div className="text-muted small">
              {stats.total_reviews} reviews
            </div>
          </div>
        </div>
      </div>

      {/* Reviews Grid */}
      <Row className="g-3">
        {reviews.slice(0, limit).map((review) => (
          <Col md={4} key={review.id}>
            <Card className="h-100 border-0 bg-light">
              <Card.Body className="p-3">
                <div className="d-flex align-items-start mb-2">
                  {renderStars(review.rating)}
                </div>
                
                {review.text && (
                  <p className="small text-muted mb-2 lh-sm">
                    "{truncateText(review.text)}"
                  </p>
                )}
                
                <div className="d-flex justify-content-between align-items-end">
                  <div>
                    <div className="fw-bold small">{review.author_name}</div>
                    <div className="text-muted" style={{ fontSize: '0.75rem' }}>
                      {formatDate(review.review_time)}
                    </div>
                  </div>
                </div>
              </Card.Body>
            </Card>
          </Col>
        ))}
      </Row>

      {/* View All Button */}
      {showViewAll && stats.total_reviews > limit && (
        <div className="text-center mt-4">
          <Button 
            variant="outline-primary" 
            size="sm"
            onClick={() => {
              // Navigate to reviews page or open modal
              const reviewsSection = document.getElementById('google-reviews');
              if (reviewsSection) {
                reviewsSection.scrollIntoView({ behavior: 'smooth' });
              }
            }}
          >
            View All {stats.total_reviews} Reviews
            <ArrowRight size={14} className="ms-1" />
          </Button>
        </div>
      )}
    </div>
  );
};

export default ReviewsWidget;