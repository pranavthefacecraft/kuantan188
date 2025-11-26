<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Admin Dashboard Routes (protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/events', [AdminDashboardController::class, 'events'])->name('events');
    Route::post('/events', [AdminDashboardController::class, 'storeEvent'])->name('events.store');
    Route::get('/events/{event}/edit', [AdminDashboardController::class, 'editEvent'])->name('events.edit');
    Route::put('/events/{event}', [AdminDashboardController::class, 'updateEvent'])->name('events.update');
    Route::post('/events/{event}/toggle-status', [AdminDashboardController::class, 'toggleEventStatus'])->name('events.toggle-status');
    Route::get('/bookings', [AdminDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/tickets', [AdminDashboardController::class, 'tickets'])->name('tickets');
    Route::post('/tickets', [AdminDashboardController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{ticket}/edit', [AdminDashboardController::class, 'editTicket'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [AdminDashboardController::class, 'updateTicket'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [AdminDashboardController::class, 'destroyTicket'])->name('tickets.destroy');
    Route::post('/tickets/bulk-delete', [AdminDashboardController::class, 'bulkDeleteTickets'])->name('tickets.bulk-delete');
    Route::get('/countries', [AdminDashboardController::class, 'countries'])->name('countries');
});
