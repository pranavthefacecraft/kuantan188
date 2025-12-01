import React, { useState, useEffect } from 'react';
import { Container, Row, Col, Card, Badge, Button, Spinner, Alert, Pagination } from 'react-bootstrap';
import { Star, StarFill, Clock, Person } from 'react-bootstrap-icons';

interface GoogleReview {
  id: number;
  google_review_id: string;
  place_id: string;
  author_name: string;
  author_photo_url?: string;
  rating: number;
  text?: string;
  review_time: string;
  like_count: number;
  reply_from_owner?: string;
  reply_time?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

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
  recent_reviews: number;
  last_updated?: string;
}

interface ReviewsResponse {
  success: boolean;
  data: GoogleReview[];
  stats: ReviewStats;
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

interface GoogleReviewsProps {
  showTitle?: boolean;
  limit?: number;
  showPagination?: boolean;
  showStats?: boolean;
  className?: string;
}

const GoogleReviews: React.FC<GoogleReviewsProps> = ({
  showTitle = true,
  limit = 10,
  showPagination = true,
  showStats = true,
  className = ''
}) => {
  const [reviews, setReviews] = useState<GoogleReview[]>([]);
  const [stats, setStats] = useState<ReviewStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });
  const [selectedRating, setSelectedRating] = useState<number | null>(null);

  // Fetch reviews from API
  const fetchReviews = async (page: number = 1, rating?: number) => {
    try {
      setLoading(true);
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: limit.toString(),
      });

      if (rating) {
        params.append('rating', rating.toString());
      }

      const apiBaseUrl = process.env.REACT_APP_API_URL || (process.env.NODE_ENV === 'production' ? 'https://admin.tfcmockup.com/api' : 'http://127.0.0.1:8000/api');
      const response = await fetch(`${apiBaseUrl}/public/reviews?${params}`);
      const data: ReviewsResponse = await response.json();

      if (data.success) {
        setReviews(data.data);
        setStats(data.stats);
        setPagination(data.pagination);
        setError(null);
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

  useEffect(() => {
    fetchReviews(currentPage, selectedRating || undefined);
  }, [currentPage, selectedRating, limit]); // eslint-disable-line react-hooks/exhaustive-deps

  // Handle page change
  const handlePageChange = (page: number) => {
    setCurrentPage(page);
  };

  // Handle rating filter
  const handleRatingFilter = (rating: number | null) => {
    setSelectedRating(rating);
    setCurrentPage(1); // Reset to first page when filtering
  };

  // Render star rating
  const renderStars = (rating: number, size: 'sm' | 'md' | 'lg' = 'sm') => {
    const stars = [];
    const sizeClass = size === 'lg' ? 'fs-4' : size === 'md' ? 'fs-5' : 'fs-6';
    
    for (let i = 1; i <= 5; i++) {
      stars.push(
        <span key={i} className={sizeClass}>
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

  // Render rating breakdown
  const renderRatingBreakdown = () => {
    if (!stats) return null;

    return (
      <Card className="mb-4">
        <Card.Body>
          <h5 className="fw-bold mb-3">Rating Overview</h5>
          <Row className="align-items-center mb-3">
            <Col xs={6}>
              <div className="text-center">
                <div className="display-4 fw-bold text-primary mb-1">
                  {stats.average_rating}
                </div>
                {renderStars(Math.round(stats.average_rating), 'lg')}
                <div className="text-muted mt-1">
                  Based on {stats.total_reviews} reviews
                </div>
              </div>
            </Col>
            <Col xs={6}>
              {[5, 4, 3, 2, 1].map((rating) => (
                <div key={rating} className="d-flex align-items-center mb-1">
                  <div className="me-2" style={{ minWidth: '60px' }}>
                    {renderStars(rating)}
                  </div>
                  <div 
                    className="progress flex-grow-1 me-2" 
                    style={{ height: '8px' }}
                  >
                    <div
                      className="progress-bar bg-warning"
                      role="progressbar"
                      style={{
                        width: `${stats.total_reviews > 0 ? (stats.rating_breakdown[rating as keyof typeof stats.rating_breakdown] / stats.total_reviews) * 100 : 0}%`
                      }}
                    />
                  </div>
                  <small className="text-muted" style={{ minWidth: '30px' }}>
                    {stats.rating_breakdown[rating as keyof typeof stats.rating_breakdown]}
                  </small>
                </div>
              ))}
            </Col>
          </Row>
          
          {/* Rating filter buttons */}
          <div className="d-flex gap-2 flex-wrap">
            <Button
              size="sm"
              variant={selectedRating === null ? 'primary' : 'outline-primary'}
              onClick={() => handleRatingFilter(null)}
            >
              All Reviews
            </Button>
            {[5, 4, 3, 2, 1].map((rating) => (
              <Button
                key={rating}
                size="sm"
                variant={selectedRating === rating ? 'warning' : 'outline-warning'}
                onClick={() => handleRatingFilter(rating)}
              >
                {renderStars(rating)} ({stats.rating_breakdown[rating as keyof typeof stats.rating_breakdown]})
              </Button>
            ))}
          </div>
        </Card.Body>
      </Card>
    );
  };

  // Format date
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Truncate text
  const truncateText = (text: string, maxLength: number = 200) => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  };

  if (loading) {
    return (
      <Container className={`py-5 ${className}`}>
        <div className="text-center">
          <Spinner animation="border" role="status" variant="primary">
            <span className="visually-hidden">Loading reviews...</span>
          </Spinner>
          <p className="mt-2 text-muted">Loading customer reviews...</p>
        </div>
      </Container>
    );
  }

  if (error) {
    return (
      <Container className={`py-5 ${className}`}>
        <Alert variant="warning" className="text-center">
          <Alert.Heading>Unable to load reviews</Alert.Heading>
          <p>{error}</p>
          <Button 
            variant="outline-warning" 
            onClick={() => fetchReviews(currentPage, selectedRating || undefined)}
          >
            Try Again
          </Button>
        </Alert>
      </Container>
    );
  }

  return (
    <Container className={`py-5 ${className}`}>
      {showTitle && (
        <Row className="mb-5">
          <Col>
            <h2 className="text-center fw-bold mb-3">
              Customer Reviews
            </h2>
            <p className="text-center text-muted lead">
              See what our visitors are saying about Menara Kuantan 188
            </p>
          </Col>
        </Row>
      )}

      {showStats && renderRatingBreakdown()}

      {reviews.length === 0 ? (
        <Row>
          <Col>
            <div className="text-center py-5">
              <h5 className="text-muted">No reviews found</h5>
              <p className="text-muted">
                {selectedRating ? `No ${selectedRating}-star reviews available` : 'No reviews available at the moment'}
              </p>
            </div>
          </Col>
        </Row>
      ) : (
        <Row>
          {reviews.map((review) => (
            <Col lg={6} key={review.id} className="mb-4">
              <Card className="h-100 border-0 shadow-sm hover-shadow">
                <Card.Body className="d-flex flex-column">
                  {/* Review Header */}
                  <div className="d-flex align-items-start mb-3">
                    <div className="me-3">
                      {review.author_photo_url ? (
                        <img
                          src={review.author_photo_url}
                          alt={review.author_name}
                          className="rounded-circle"
                          style={{ width: '50px', height: '50px', objectFit: 'cover' }}
                          onError={(e) => {
                            const target = e.target as HTMLImageElement;
                            target.style.display = 'none';
                          }}
                        />
                      ) : (
                        <div 
                          className="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                          style={{ width: '50px', height: '50px' }}
                        >
                          <Person size={24} />
                        </div>
                      )}
                    </div>
                    <div className="flex-grow-1">
                      <h6 className="fw-bold mb-1">{review.author_name}</h6>
                      <div className="d-flex align-items-center mb-1">
                        {renderStars(review.rating)}
                        <Badge bg="primary" className="ms-2">
                          {review.rating}/5
                        </Badge>
                      </div>
                      <small className="text-muted d-flex align-items-center">
                        <Clock size={14} className="me-1" />
                        {formatDate(review.review_time)}
                      </small>
                    </div>
                  </div>

                  {/* Review Content */}
                  {review.text && (
                    <div className="flex-grow-1 mb-3">
                      <p className="mb-0 text-break">
                        {truncateText(review.text)}
                      </p>
                    </div>
                  )}

                  {/* Owner Reply */}
                  {review.reply_from_owner && (
                    <div className="mt-3 p-3 bg-light rounded-3">
                      <small className="fw-bold text-primary d-block mb-1">
                        Response from Menara Kuantan 188
                      </small>
                      <small className="text-muted">
                        {review.reply_from_owner}
                      </small>
                    </div>
                  )}
                </Card.Body>
              </Card>
            </Col>
          ))}
        </Row>
      )}

      {/* Pagination */}
      {showPagination && pagination.last_page > 1 && (
        <Row className="mt-5">
          <Col>
            <div className="d-flex justify-content-center">
              <Pagination>
                <Pagination.Prev 
                  disabled={currentPage === 1}
                  onClick={() => handlePageChange(currentPage - 1)}
                />
                
                {[...Array(Math.min(pagination.last_page, 5))].map((_, index) => {
                  const pageNum = currentPage <= 3 
                    ? index + 1 
                    : currentPage + index - 2;
                  
                  if (pageNum <= pagination.last_page && pageNum > 0) {
                    return (
                      <Pagination.Item
                        key={pageNum}
                        active={pageNum === currentPage}
                        onClick={() => handlePageChange(pageNum)}
                      >
                        {pageNum}
                      </Pagination.Item>
                    );
                  }
                  return null;
                })}
                
                <Pagination.Next 
                  disabled={currentPage === pagination.last_page}
                  onClick={() => handlePageChange(currentPage + 1)}
                />
              </Pagination>
            </div>
            
            <div className="text-center mt-3">
              <small className="text-muted">
                Showing {reviews.length} of {pagination.total} reviews
              </small>
            </div>
          </Col>
        </Row>
      )}
    </Container>
  );
};

export default GoogleReviews;