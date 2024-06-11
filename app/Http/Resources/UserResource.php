<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 * @mixin \App\Models\Country
 */
class UserResource extends JsonResource
{
    public static $wrap = '';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authority = ['user', $this->country->name];
        if ($this->vendor){
            array_push($authority, 'vendor');
        }
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'username' => $this->username,
            'email' => $this->email,
            'referral_balance' => $this->referral_balance,
            'activities_balance' => $this->activities_balance,
            'indirect_balance' => $this->indirect_balance,
            'bet_balance' => $this->bet_balance,
            'verified' => $this->verified,
            'authority' => $authority,
            'plan' => $this->plan->name,
            'current_level' => new ReferralLevelResource($this->referralLevel),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
