<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Kuantan188') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Material Design Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --background: #f8fafc;
            --surface: #ffffff;
            --surface-variant: #f1f5f9;
            --on-surface: #0f172a;
            --on-surface-variant: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --glassmorphism: rgba(255, 255, 255, 0.7);
            --glassmorphism-border: rgba(255, 255, 255, 0.18);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--on-surface);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--glassmorphism);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--glassmorphism-border);
            box-shadow: var(--shadow-lg);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--on-surface-variant);
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: var(--on-surface-variant);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary);
            color: white;
            transform: translateX(4px);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            background: var(--background);
        }

        /* Header */
        .main-header {
            background: var(--surface);
            box-shadow: var(--shadow);
            padding: 1rem 2rem;
            display: flex;
            justify-content: between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--on-surface);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--on-surface-variant);
            border: 1px solid var(--border);
        }

        .btn-outline:hover {
            background: var(--surface-variant);
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
        }

        /* Cards */
        .card {
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface-variant);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--on-surface);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--surface) 0%, var(--glassmorphism) 100%);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glassmorphism-border);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--on-surface-variant);
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--on-surface);
            line-height: 1;
        }

        .stat-change {
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--error);
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table th {
            font-weight: 600;
            color: var(--on-surface-variant);
            background: var(--surface-variant);
        }

        .table tr:hover {
            background: var(--surface-variant);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        /* Grid Layout */
        .grid {
            display: grid;
            gap: 1.5rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .main-header {
                padding: 1rem;
            }

            .content-area {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>

    @yield('styles')
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                    <span class="material-icons">dashboard</span>
                    Kuantan188 Admin
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <div class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="material-icons nav-icon">dashboard</span>
                            Dashboard
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <div class="nav-item">
                        <a href="{{ route('admin.events') }}" 
                           class="nav-link {{ request()->routeIs('admin.events*') ? 'active' : '' }}">
                            <span class="material-icons nav-icon">event</span>
                            Events
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('admin.tickets') }}" 
                           class="nav-link {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
                            <span class="material-icons nav-icon">confirmation_number</span>
                            Tickets
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('admin.bookings') }}" 
                           class="nav-link {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                            <span class="material-icons nav-icon">book_online</span>
                            Bookings
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('admin.countries') }}" 
                           class="nav-link {{ request()->routeIs('admin.countries*') ? 'active' : '' }}">
                            <span class="material-icons nav-icon">public</span>
                            Countries
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <div class="nav-item">
                        <a href="#" class="nav-link">
                            <span class="material-icons nav-icon">settings</span>
                            Settings
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('logout') }}" 
                           class="nav-link"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span class="material-icons nav-icon">logout</span>
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <h1 class="header-title">@yield('title', 'Dashboard')</h1>
                
                <div class="header-actions">
                    <span class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</span>
                    <a href="#" class="btn btn-outline">
                        <span class="material-icons" style="font-size: 18px;">notifications</span>
                    </a>
                    <a href="#" class="btn btn-outline">
                        <span class="material-icons" style="font-size: 18px;">account_circle</span>
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="content-area">
                @if (session('success'))
                    <div class="alert alert-success mb-4" style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error mb-4" style="background: rgba(239, 68, 68, 0.1); color: var(--error); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    
    <script>
        // Theme Colors for Charts
        window.chartColors = {
            primary: '#6366f1',
            secondary: '#8b5cf6',
            accent: '#06b6d4',
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444'
        };

        // Format Currency Helper
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-MY', {
                style: 'currency',
                currency: 'MYR'
            }).format(amount);
        }

        // Format Number Helper
        function formatNumber(number) {
            return new Intl.NumberFormat().format(number);
        }
    </script>

    @yield('scripts')
</body>
</html>