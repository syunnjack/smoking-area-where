<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 外部データ（OpenStreetMap）から取り込んだ喫煙所と、利用者が投稿した
// 喫煙所を区別できるようにする。再取り込みで重複させないための鍵も兼ねる。
// あわせて、元データにある住所・屋内屋外・利用時間を持てるようにする。
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spots', function (Blueprint $table) {
            $table->string('address')->nullable()->after('description');
            $table->string('facility_type', 20)->nullable()->after('address');
            $table->string('opening_hours')->nullable()->after('facility_type');
            $table->string('source', 20)->nullable()->after('likes_count');
            $table->string('source_ref')->nullable()->after('source');

            $table->unique(['source', 'source_ref']);
        });
    }

    public function down(): void
    {
        Schema::table('spots', function (Blueprint $table) {
            $table->dropUnique(['source', 'source_ref']);
            $table->dropColumn(['address', 'facility_type', 'opening_hours', 'source', 'source_ref']);
        });
    }
};
