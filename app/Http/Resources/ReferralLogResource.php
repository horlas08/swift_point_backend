<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReferralLog */
class ReferralLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->user->username ?? $this->user->firstname,
            'image' => $this->user->image,
            'date' => $this->user->created_at,
        ];
    }
}
