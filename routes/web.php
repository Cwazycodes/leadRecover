<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadInteractionController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketing + booking
|--------------------------------------------------------------------------
*/
Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('pricing');

Route::get('/book/{business}', [BookingController::class, 'show'])->name('book');
Route::post('/book/{business}', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('book.store');

/*
|--------------------------------------------------------------------------
| Authenticated tenant application
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');

    // Billing stays reachable even without an active plan.
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
        Route::post('/swap', [BillingController::class, 'swap'])->name('swap');
        Route::post('/cancel', [BillingController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [BillingController::class, 'resume'])->name('resume');
        Route::get('/portal', [BillingController::class, 'portal'])->name('portal');
    });

    // Core features require an active subscription / trial.
    Route::middleware('subscribed')->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
        Route::post('/leads/{lead}/messages', [LeadInteractionController::class, 'store'])->name('leads.messages.store');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/templates', [SettingsController::class, 'updateTemplates'])->name('settings.templates');
        Route::put('/settings/booking', [SettingsController::class, 'updateBooking'])->name('settings.booking');
        Route::put('/settings/hours', [SettingsController::class, 'updateHours'])->name('settings.hours');
    });
});

/*
|--------------------------------------------------------------------------
| Profile (any authenticated user, incl. platform admins)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Platform admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'platform-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{business}', [Admin\CustomerController::class, 'show'])->name('customers.show');
    });

require __DIR__.'/auth.php';
