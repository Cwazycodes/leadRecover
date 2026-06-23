<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A unified timeline entry for everything that happens on a lead:
 * outbound SMS/WhatsApp, inbound replies, system events and staff notes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // sms | whatsapp | voice | email | note | system
            $table->string('channel');
            // inbound | outbound | system
            $table->string('direction');
            $table->text('body')->nullable();
            // queued | sent | delivered | failed | received | read
            $table->string('status')->nullable();
            $table->string('provider_sid')->nullable()->index();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
