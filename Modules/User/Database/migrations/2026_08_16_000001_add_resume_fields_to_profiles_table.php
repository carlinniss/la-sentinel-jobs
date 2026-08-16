<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('resume_path')->nullable()->after('is_verified');
            $table->string('resume_original_name')->nullable()->after('resume_path');
            $table->string('resume_mime')->nullable()->after('resume_original_name');
            $table->unsignedBigInteger('resume_size')->nullable()->after('resume_mime');
            $table->timestamp('resume_uploaded_at')->nullable()->after('resume_size');
            $table->boolean('resume_searchable')->default(false)->after('resume_uploaded_at');
            $table->boolean('resume_access_enabled')->default(false)->after('resume_searchable');
            $table->unsignedInteger('resume_fee_cents')->default(0)->after('resume_access_enabled');
            $table->timestamp('resume_paid_at')->nullable()->after('resume_fee_cents');
            $table->string('resume_checkout_session_id')->nullable()->after('resume_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'resume_path',
                'resume_original_name',
                'resume_mime',
                'resume_size',
                'resume_uploaded_at',
                'resume_searchable',
                'resume_access_enabled',
                'resume_fee_cents',
                'resume_paid_at',
                'resume_checkout_session_id',
            ]);
        });
    }
};
