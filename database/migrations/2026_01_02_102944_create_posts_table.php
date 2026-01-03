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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title'); // Khớp với formData.title
            $table->string('slug')->unique();
            $table->string('type')->default('html'); // Khớp với formData.type
            $table->text('excerpt')->nullable(); // Khớp với formData.excerpt
            $table->longText('content'); // Khớp với formData.content (Lưu mã HTML)
            $table->string('image')->nullable(); // Khớp với formData.image (URL hình ảnh)
            $table->string('author')->default('Admin'); // Khớp với formData.author
            $table->string('read_time')->nullable(); // Khớp với post.read_time (Ví dụ: "10 min")
            $table->softDeletes();
            $table->enum('status', ['draft', 'published'])->default('published');

            $table->timestamps(); // Sẽ tự tạo created_at khớp với post.created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
