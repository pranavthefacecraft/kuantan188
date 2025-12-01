@extends('layouts.admin')

@section('title', 'Google Reviews Management')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="content-title">Google Reviews Management</h1>
            <p class="content-subtitle">Manage and sync Google Reviews for Menara Kuantan 188</p>
        </div>
        <div>
            <form action="{{ route('admin.reviews.sync') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to sync Google Reviews? This may take a few moments.')">
                    <span class="material-icons me-2">sync</span>
                    Sync Google Reviews
                </button>
            </form>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="material-icons me-2">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="material-icons me-2">error</span>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('sync_output'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Sync Output:</strong>
        <pre class="mt-2">{{ session('sync_output') }}</pre>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistics Cards -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-3">
            <div class="card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1 text-white-50">Total Reviews</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_reviews'] }}</h3>
                        </div>
                        <span class="material-icons" style="font-size: 2rem; opacity: 0.4;">star</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1 text-white-50">Active Reviews</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['active_reviews'] }}</h3>
                        </div>
                        <span class="material-icons" style="font-size: 2rem; opacity: 0.4;">visibility</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card bg-warning text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1 text-white-50">Average Rating</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['average_rating'], 1) }}</h3>
                        </div>
                        <span class="material-icons" style="font-size: 2rem; opacity: 0.4;">star_rate</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card bg-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1 text-white-50">Last Sync</h6>
                            <p class="mb-0 fw-bold h5">
                                @if($stats['latest_sync'])
                                    {{ $stats['latest_sync']->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                        <span class="material-icons" style="font-size: 2rem; opacity: 0.4;">access_time</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviews Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <span class="material-icons me-2">rate_review</span>
            All Reviews ({{ $reviews->total() }})
        </h5>
    </div>
    <div class="card-body p-0">
        @if($reviews->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Author</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr class="{{ !$review->is_active ? 'table-secondary' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($review->author_photo_url)
                                            <img src="{{ $review->author_photo_url }}" 
                                                 alt="{{ $review->author_name }}" 
                                                 class="rounded-circle me-2"
                                                 style="width: 32px; height: 32px;">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ substr($review->author_name, 0, 2) }}
                                            </div>
                                        @endif
                                        <strong>{{ $review->author_name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <span class="material-icons text-warning" style="font-size: 16px;">star</span>
                                            @else
                                                <span class="material-icons text-muted" style="font-size: 16px;">star_border</span>
                                            @endif
                                        @endfor
                                        <span class="ms-1 text-muted">({{ $review->rating }})</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        <p class="mb-0 text-truncate" title="{{ $review->text }}">
                                            {{ Str::limit($review->text, 80) }}
                                        </p>
                                        @if($review->like_count > 0)
                                            <small class="text-muted">
                                                <span class="material-icons" style="font-size: 14px;">thumb_up</span>
                                                {{ $review->like_count }} likes
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $review->review_time->format('M j, Y') }}<br>
                                        {{ $review->review_time->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    @if($review->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Hidden</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.reviews.toggle-status', $review) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm {{ $review->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                title="{{ $review->is_active ? 'Hide Review' : 'Show Review' }}">
                                            <span class="material-icons" style="font-size: 16px;">
                                                {{ $review->is_active ? 'visibility_off' : 'visibility' }}
                                            </span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @if($review->reply_from_owner)
                                <tr class="table-light">
                                    <td colspan="6">
                                        <div class="ms-4">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="material-icons text-primary me-1" style="font-size: 16px;">business</span>
                                                <strong class="text-primary">Owner Response:</strong>
                                                <small class="text-muted ms-2">{{ $review->reply_time->format('M j, Y') }}</small>
                                            </div>
                                            <p class="mb-0 text-muted">{{ $review->reply_from_owner }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <span class="material-icons text-muted" style="font-size: 4rem;">star_border</span>
                <h5 class="text-muted mt-2">No Reviews Found</h5>
                <p class="text-muted">Click "Sync Google Reviews" to fetch reviews from Google Places API</p>
            </div>
        @endif
    </div>
    
    @if($reviews->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $reviews->links() }}
            </div>
        </div>
    @endif
</div>

<style>
.content-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

/* Force horizontal layout for statistics cards */
.row {
    display: flex !important;
    flex-wrap: wrap !important;
}

.col-3 {
    flex: 0 0 25% !important;
    max-width: 25% !important;
    padding-right: 15px;
    padding-left: 15px;
}

@media (max-width: 768px) {
    .col-3 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }
}

@media (max-width: 576px) {
    .col-3 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
}

.content-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.content-subtitle {
    opacity: 0.9;
    margin-bottom: 0;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.alert {
    border: none;
    border-radius: 10px;
}
</style>
@endsection