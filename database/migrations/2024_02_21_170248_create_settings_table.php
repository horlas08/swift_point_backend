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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->double('mining');
            $table->double('ads');
            $table->double('username_point');
            $table->double('twitter_point');
            $table->string('youtube_url');
            $table->string('twitter_url');
            $table->double('telegram_point');
            $table->string('telegram_url');
            $table->double('profile_point');
            $table->double('youtube_point');
            $table->double('daily_point');
            $table->integer('referral_level_no');
            $table->double('facebook_point');
            $table->string('facebook_url');
            $table->double('referral_level_up');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
