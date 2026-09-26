<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citoyens', function (Blueprint $table) {
            if (!Schema::hasColumn('citoyens', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (!Schema::hasColumn('citoyens', 'email_verification_token')) {
                $table->string('email_verification_token')->nullable()->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citoyens', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'email_verification_token']);
        });
    }
};