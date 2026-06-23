<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leads are the heart of LeadRecover: a recoverable opportunity created
 * (most often) from a missed call. Every lead is hard-scoped to a business.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();

            // missed_call | manual | whatsapp | web_form | other
            $table->string('source')->default('missed_call');
            // new | contacted | booked | converted | lost | stale
            $table->string('status')->default('new');

            $table->text('notes')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->decimal('estimated_value', 10, 2)->nullable();

            $table->unsignedTinyInteger('follow_up_stage')->default(0);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'phone']);
            $table->index(['business_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
