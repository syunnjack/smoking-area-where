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
        Schema::create('spots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('area')->nullable();
            $table->json('congestion_reports')->nullable(); // JSON型で混雑度報告を保存
            $table->float('average_congestion')->nullable(); // 平均混雑度
            $table->unsignedInteger('views')->default(0); // views カラムを追加
            $table->unsignedInteger('likes_count')->default(0); // likes_count カラムを追加
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
};
