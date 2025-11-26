@extends('layouts.app')

@section('title', 'Book Event - ' . $event->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Event Header -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            @if($event->image_url)
                <img src="{{ asset($event->image_url) }}" alt="{{ $event->title }}" class="w-full h-64 object-cover">
            @endif
            
            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $event->title }}</h1>
                <p class="text-gray-600 mb-4">{{ $event->description }}</p>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-semibold text-gray-700">📅 Date:</span>
                        <span class="text-gray-600">{{ $event->event_date->format('M j, Y g:i A') }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700">📍 Location:</span>
                        <span class="text-gray-600">{{ $event->location }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Book Your Tickets</h2>
            
            <form id="bookingForm" method="POST" action="{{ route('customer.book-event', $event->id) }}">
                @csrf
                
                <!-- Quantity Selector -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Number of People</label>
                    
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 border">
                        <div class="flex items-center">
                            <span class="material-icons text-blue-600 mr-2">group</span>
                            <div>
                                <div class="font-semibold text-gray-800">Add People</div>
                                <div class="text-sm text-gray-600">Friends, family, colleagues</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <button type="button" onclick="decreaseQuantity()" 
                                    class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50" 
                                    id="decreaseBtn">
                                <span class="material-icons text-gray-600">remove</span>
                            </button>
                            
                            <span class="text-2xl font-bold text-blue-600 min-w-[3rem] text-center" id="quantityDisplay">1</span>
                            <input type="hidden" name="quantity" id="quantityInput" value="1">
                            
                            <button type="button" onclick="increaseQuantity()" 
                                    class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center hover:bg-gray-50" 
                                    id="increaseBtn">
                                <span class="material-icons text-gray-600">add</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="mb-6 bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Price per person:</span>
                        <span class="font-semibold">RM {{ number_format($event->price, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Number of people:</span>
                        <span class="font-semibold" id="quantitySummary">1</span>
                    </div>
                    <hr class="my-2">
                    <div class="flex justify-between items-center text-lg font-bold text-blue-600">
                        <span>Total Amount:</span>
                        <span id="totalAmount">RM {{ number_format($event->price, 2) }}</span>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="name" id="name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                            <input type="email" name="email" id="email" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <select name="country_id" id="country" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                    <span class="material-icons inline-block mr-2">event_available</span>
                    Book Now - <span id="submitTotal">RM {{ number_format($event->price, 2) }}</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .material-icons {
        font-family: 'Material Icons';
        font-weight: normal;
        font-style: normal;
        font-size: 24px;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
    }
</style>

<script>
    const pricePerPerson = {{ $event->price }};
    let currentQuantity = 1;

    function updateQuantity(newQuantity) {
        currentQuantity = Math.max(1, Math.min(10, newQuantity)); // Min 1, Max 10
        
        // Update UI elements
        document.getElementById('quantityDisplay').textContent = currentQuantity;
        document.getElementById('quantityInput').value = currentQuantity;
        document.getElementById('quantitySummary').textContent = currentQuantity;
        
        // Calculate total
        const total = pricePerPerson * currentQuantity;
        const formattedTotal = 'RM ' + total.toFixed(2);
        
        document.getElementById('totalAmount').textContent = formattedTotal;
        document.getElementById('submitTotal').textContent = formattedTotal;
        
        // Update button states
        document.getElementById('decreaseBtn').disabled = currentQuantity <= 1;
        document.getElementById('increaseBtn').disabled = currentQuantity >= 10;
    }

    function increaseQuantity() {
        updateQuantity(currentQuantity + 1);
    }

    function decreaseQuantity() {
        updateQuantity(currentQuantity - 1);
    }

    // Initialize
    updateQuantity(1);
</script>
@endsection