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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('popular_suggestion_id'); // Khóa ngoại tham chiếu tới bảng popular_suggestions
            $table->integer('day'); // Ngày của hoạt động
            $table->string('place'); // Nơi sẽ đi
            $table->string('meal')->nullable(); // Ăn gì (nullable)
            $table->text('description')->nullable(); // Mô tả hoạt động
            $table->timestamps();

            // Thiết lập khóa ngoại
            $table->foreign('popular_suggestion_id')->references('id')->on('popular_suggestions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
