<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PublicEventController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\BillplzController;
use App\Http\Controllers\TestPaymentController;

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Public routes
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/public/countries/currencies', [CountryController::class, 'getCurrencies']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

// Public events for frontend website
Route::get('/public/events', [PublicEventController::class, 'index']);
Route::get('/public/events/featured', [PublicEventController::class, 'featured']);
Route::get('/public/events/book-now', [PublicEventController::class, 'bookNow']);
Route::get('/public/events/{id}', [PublicEventController::class, 'show']);
Route::get('/public/tickets', [PublicEventController::class, 'getTickets']);
Route::get('/public/tickets/{id}', [PublicEventController::class, 'getTicketById']);
Route::get('/public/events/debug', [PublicEventController::class, 'debug']);

// Public bookings API
Route::get('/public/bookings/test', [App\Http\Controllers\PublicBookingController::class, 'test']);
Route::get('/public/bookings/test-create', [App\Http\Controllers\PublicBookingController::class, 'testCreate']);
Route::post('/public/bookings', [App\Http\Controllers\PublicBookingController::class, 'store']);
Route::get('/public/bookings', [App\Http\Controllers\PublicBookingController::class, 'getBookingsByEmail']);

// Public Google Reviews routes
Route::get('/public/reviews', [ReviewController::class, 'index']);
Route::get('/public/reviews/stats', [ReviewController::class, 'stats']);

// Public Billplz Payment routes
Route::post('/public/payment/billplz/create', [BillplzController::class, 'createPayment']);
Route::post('/public/payment/billplz/callback', [BillplzController::class, 'handleCallback']);
Route::get('/public/payment/billplz/status/{billId}', [BillplzController::class, 'checkPaymentStatus']);
Route::get('/public/payment/billplz/test', [BillplzController::class, 'testConnection']);
Route::get('/public/payment/billplz/debug-url', [BillplzController::class, 'debugPaymentUrl']);
Route::get('/public/payment/billplz/booking/{bookingId}', [BillplzController::class, 'getPaymentDetails']);
Route::post('/public/payment/billplz/cancel/{billId}', [BillplzController::class, 'cancelPayment']);

Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
Route::get('/events/{event}/countries/{country}/tickets', [TicketController::class, 'getByEventAndCountry']);

// Booking routes
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/reference/{reference}', [BookingController::class, 'getByReference']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Admin routes for countries
    Route::apiResource('countries', CountryController::class)->except(['index']);
    
    // Admin routes for events
    Route::apiResource('events', EventController::class)->except(['index', 'show']);
    
    // Admin routes for tickets
    Route::apiResource('tickets', TicketController::class)->except(['index', 'show']);
    
    // Admin routes for bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    
    // Admin routes for Google Reviews
    Route::post('/reviews/fetch', [ReviewController::class, 'fetchFromGoogle']);
    Route::put('/reviews/{review}/toggle-status', [ReviewController::class, 'toggleStatus']);
});

// Test payment route (temporary)
Route::get('/test/payment/billplz', [TestPaymentController::class, 'testPaymentCreation']);