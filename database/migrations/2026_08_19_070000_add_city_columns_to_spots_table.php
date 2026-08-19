<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 喫煙所に市区町村と町名を持たせる。
     *
     * OpenStreetMap の喫煙所はほとんどが名前「喫煙所」だけで住所も無く、
     * そのままでは900件近いページがすべて同じ見出しになってしまう。
     * 座標から求めた行政区画名を持たせ、「喫煙所（仙台市青葉区中央）」のように
     * 場所が分かる表示にする。
     */
    public function up(): void
    {
        Schema::table('spots', function (Blueprint $table) {
            $table->string('city', 40)->nullable()->after('area');
            $table->string('town', 60)->nullable()->after('city');

            $table->index(['area', 'city']);
        });
    }

    public function down(): void
    {
        Schema::table('spots', function (Blueprint $table) {
            $table->dropIndex(['area', 'city']);
            $table->dropColumn(['city', 'town']);
        });
    }
};
