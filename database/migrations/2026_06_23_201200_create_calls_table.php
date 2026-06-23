<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raw telephony records from Twilio. A missed/no-answer call typically
 * spawns exactly one lead, linked via lead_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();

            $table->string('call_sid')->nullable()->unique();
            $table->string('from_number');
            $table->string('to_number');
            $table->string('direction')->default('inbound');
            // missed | completed | no-answer | busy | failed | voicemail
            $table->string('status')->default('missed');
            $table->unsignedInteger('duration')->nullable();
            $table->string('recording_url')->nullable();
            $table->timestamp('occurred_at')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
