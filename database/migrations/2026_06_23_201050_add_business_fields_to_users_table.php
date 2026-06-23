<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attach users to a tenant (business) and add role / platform-admin flags.
 * A null business_id with is_platform_admin = true represents a platform
 * owner who manages the whole SaaS rather than a single business.
 *
 * Note: we intentionally avoid a foreign-key constraint here so the
 * migration stays compatible with SQLite (used for the test suite), which
 * cannot add FK constraints to an existing table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->index();
            $table->string('role')->default('owner')->after('password'); // owner | admin | staff
            $table->boolean('is_platform_admin')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['business_id', 'role', 'is_platform_admin']);
        });
    }
};
