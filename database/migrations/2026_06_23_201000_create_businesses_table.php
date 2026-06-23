<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The "businesses" table is the tenant boundary for the whole platform.
 * Every piece of customer data (leads, calls, interactions, users,
 * subscriptions) hangs off a business row. Cashier billing columns live
 * here too, so the Business model is the Stripe billable entity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();              // public contact number
            $table->string('twilio_number')->nullable()->index(); // number that receives missed calls
            $table->string('forward_to_number')->nullable();  // where incoming calls are forwarded
            $table->string('logo_path')->nullable();
            $table->string('industry')->nullable();
            $table->string('timezone')->default('Europe/London');

            // Booking configuration
            $table->string('booking_type')->default('internal'); // internal | url | calendly
            $table->string('booking_url')->nullable();
            $table->string('calendly_url')->nullable();

            // Messaging templates (support {{name}}, {{business}}, {{booking_link}} tokens)
            $table->text('sms_template')->nullable();
            $table->text('whatsapp_template')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('auto_respond_enabled')->default(true);

            // Flexible JSON columns
            $table->json('opening_hours')->nullable();
            $table->json('settings')->nullable();

            $table->timestamp('onboarded_at')->nullable();

            // Laravel Cashier (Stripe) billing columns
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
