<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('employer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 32);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['profile_id', 'created_at']);
            $table->index(['employer_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_views');
    }
};
