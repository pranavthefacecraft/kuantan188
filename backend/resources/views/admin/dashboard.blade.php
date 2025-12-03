@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="grid">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Bookings</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <span class="material-icons">book_online</span>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['totalBookings']) }}</div>
            <div class="stat-change positive">
                <span class="material-icons" style="font-size: 12px;">trending_up</span>
                +{{ $stats['todayBookings'] }} today
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Revenue</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                    <span class="material-icons">attach_money</span>
                </div>
            </div>
            <div class="stat-value">RM {{ number_format($stats['totalRevenue'], 2) }}</div>
            <div class="stat-change positive">
                <span class="material-icons" style="font-size: 12px;">trending_up</span>
                +8.2% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Active Events</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <span class="material-icons">event</span>
                </div>
            </div>
            <div class="stat-value">{{ $stats['activeEvents'] }}</div>
            <div class="stat-change positive">
                <span class="material-icons" style="font-size: 12px;">trending_up</span>
                {{ $stats['totalTickets'] }} tickets available
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Pending Bookings</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #ec4899);">
                    <span class="material-icons">pending</span>
                </div>
            </div>
            <div class="stat-value">{{ $stats['pendingBookings'] }}</div>
            <div class="stat-change {{ $stats['pendingBookings'] > 0 ? 'negative' : 'positive' }}">
                <span class="material-icons" style="font-size: 12px;">
                    {{ $stats['pendingBookings'] > 0 ? 'trending_down' : 'check_circle' }}
                </span>
                {{ $stats['pendingBookings'] > 0 ? 'Needs attention' : 'All clear' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Total Customers</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">
                    <span class="material-icons">people</span>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['totalCustomers']) }}</div>
            <div class="stat-change positive">
                <span class="material-icons" style="font-size: 12px;">trending_up</span>
                Growing steadily
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Countries</div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #84cc16, #10b981);">
                    <span class="material-icons">public</span>
                </div>
            </div>
            <div class="stat-value">{{ $stats['totalCountries'] }}</div>
            <div class="stat-change positive">
                <span class="material-icons" style="font-size: 12px;">language</span>
                Multi-region support
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-2">
        <!-- Booking Trends Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Booking Trends (Last 30 Days)</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="bookingTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Event Distribution Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Event Distribution by Country</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="eventDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Revenue Trends (Last 12 Months)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height: 400px;">
                <canvas id="revenueTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Bookings</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking Reference</th>
                            <th>Event</th>
                            <th>Customer</th>
                            <th>Country</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->booking_reference }}</strong>
                                </td>
                                <td>{{ $booking->event->title ?? 'N/A' }}</td>
                                <td>
                                    <div>
                                        <div>{{ $booking->customer_name }}</div>
                                        <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                            {{ $booking->customer_email }}
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $booking->country->name ?? 'N/A' }}</td>
                                <td>RM {{ number_format($booking->total_amount, 2) }}</td>
                                <td>
                                    @if($booking->status === 'confirmed')
                                        <span class="badge badge-success">
                                            <span class="material-icons" style="font-size: 12px;">check_circle</span>
                                            Confirmed
                                        </span>
                                    @elseif($booking->status === 'pending')
                                        <span class="badge badge-warning">
                                            <span class="material-icons" style="font-size: 12px;">schedule</span>
                                            Pending
                                        </span>
                                    @else
                                        <span class="badge badge-error">
                                            <span class="material-icons" style="font-size: 12px;">cancel</span>
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $booking->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--on-surface-variant);">
                                    No bookings found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Booking Trends Chart
        const bookingTrendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
        const bookingTrendsData = @json($bookingTrends);
        
        new Chart(bookingTrendsCtx, {
            type: 'line',
            data: {
                labels: bookingTrendsData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [
                    {
                        label: 'Bookings',
                        data: bookingTrendsData.map(item => item.bookings),
                        borderColor: window.chartColors.primary,
                        backgroundColor: window.chartColors.primary + '20',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Revenue (RM)',
                        data: bookingTrendsData.map(item => item.revenue),
                        borderColor: window.chartColors.success,
                        backgroundColor: window.chartColors.success + '20',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Event Distribution Chart
        const eventDistributionCtx = document.getElementById('eventDistributionChart').getContext('2d');
        const eventDistributionData = @json($eventDistribution);
        
        new Chart(eventDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: eventDistributionData.map(item => item.name),
                datasets: [{
                    data: eventDistributionData.map(item => item.count),
                    backgroundColor: [
                        window.chartColors.primary,
                        window.chartColors.secondary,
                        window.chartColors.accent,
                        window.chartColors.success,
                        window.chartColors.warning,
                        window.chartColors.error
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Revenue Trends Chart
        const revenueTrendsCtx = document.getElementById('revenueTrendsChart').getContext('2d');
        const revenueTrendsData = @json($revenueTrends);
        
        new Chart(revenueTrendsCtx, {
            type: 'bar',
            data: {
                labels: revenueTrendsData.map(item => item.month),
                datasets: [{
                    label: 'Revenue (RM)',
                    data: revenueTrendsData.map(item => item.revenue),
                    backgroundColor: gradient => {
                        const ctx = gradient.chart.ctx;
                        const gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
                        gradientFill.addColorStop(0, window.chartColors.primary + 'AA');
                        gradientFill.addColorStop(1, window.chartColors.primary + '33');
                        return gradientFill;
                    },
                    borderColor: window.chartColors.primary,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'RM ' + new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection