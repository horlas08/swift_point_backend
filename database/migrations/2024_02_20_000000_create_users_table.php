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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('username')->nullable()->unique();
            $table->string('image')->nullable();
            $table->string('email')->unique();
            $table->string('youtube_email')->nullable()->unique();
            $table->string('twitter_username')->nullable()->unique();
            $table->string('telegram_username')->nullable()->unique();
            $table->string('facebook_username')->nullable()->unique();
            $table->boolean('verified')->default(true);
            $table->decimal('balance')->default(0);
            $table->dateTime('last_mining')->default(\Carbon\Carbon::parse(now()->toDateTimeString())->subDay(3));
            $table->dateTime('last_login')->default(now());
            $table->integer('no_of_referral')->default(0);
            $table->integer('streak_count')->default(0);
            $table->boolean('referral_level_up')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->unsignedBigInteger('referral_id')->nullable();
            $table->unsignedBigInteger('referral_code')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('referral_id')->on('users')
                ->references('id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
