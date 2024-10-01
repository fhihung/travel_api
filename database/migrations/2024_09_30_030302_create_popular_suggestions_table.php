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
        Schema::create('popular_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('location'); // Địa điểm du lịch
            $table->integer('days'); // Số ngày của kế hoạch du lịch
            $table->integer('people'); // Số người tham gia
            $table->decimal('cost_estimate', 10, 2); // Ước lượng chi phí
            $table->json('hotels')->nullable(); // Lưu thông tin khách sạn
            $table->json('transportation')->nullable(); // Lưu thông tin phương tiện di chuyển
            $table->text('description')->nullable(); // Mô tả hoạt động
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular_suggestions');
    }
};
