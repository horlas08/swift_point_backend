<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ads' => 200,
            'mining' => 300,
            'username_point' => 200,
            'twitter_point' => 200,
            'twitter_url' => 'https://twitter.com/',
            'youtube_url' => 'https://youtube.com/',
            'telegram_point' => 200,
            'youtube_point' => 200,
            'telegram_url' => 'https://twitter.com',
            'facebook_point' => 200,
            'facebook_url' => 'https://twitter.com',
            'profile_point' => 200,
            'daily_point' => 200,
            'referral_level_no' => 5,
            'referral_level_up' => 200,
        ];
    }
}
