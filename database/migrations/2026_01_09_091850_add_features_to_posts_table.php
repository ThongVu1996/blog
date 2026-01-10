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
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('status');
            $table->boolean('is_featured')->default(false)->after('views_count');
            $table->unsignedTinyInteger('featured_order')->nullable()->after('is_featured');
            
            // Add index for performance
            $table->index('views_count');
            $table->index(['is_featured', 'featured_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['posts_views_count_index']);
            $table->dropIndex(['posts_is_featured_featured_order_index']);
            $table->dropColumn(['views_count', 'is_featured', 'featured_order']);
        });
    }
};
