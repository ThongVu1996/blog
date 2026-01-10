<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at');
            
            // Indexes for performance
            $table->index('post_id');
            $table->index('ip_address');
            $table->index('viewed_at');
            $table->index(['post_id', 'ip_address', 'viewed_at']); // Composite index for spam check
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
