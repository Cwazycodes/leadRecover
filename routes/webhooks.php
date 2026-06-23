<?php

use App\Http\Controllers\Webhooks\TwilioMessageController;
use App\Http\Controllers\Webhooks\TwilioVoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
| Stateless inbound webhooks. Registered (in bootstrap/app.php) outside the
| "web" group so they carry no CSRF/session middleware, and guarded by the
| Twilio signature verification middleware.
|
| Stripe's webhook (/stripe/webhook) is registered automatically by Cashier
| with its own signature verification.
*/

Route::middleware('twilio')->group(function () {
    Route::post('twilio/voice', [TwilioVoiceController::class, 'incoming'])->name('twilio.voice');
    Route::post('twilio/voice/status', [TwilioVoiceController::class, 'statusCallback'])->name('twilio.voice.status');
    Route::post('twilio/message', [TwilioMessageController::class, 'incoming'])->name('twilio.message');
    Route::post('twilio/message/status', [TwilioMessageController::class, 'statusCallback'])->name('twilio.message.status');
});
