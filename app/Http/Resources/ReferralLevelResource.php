<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralLevelResource extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hint' => $this->hint,
            'totalReferral' => $this->no_of_ref,
            'completed' => $this->getCompletedStatus(),
            'completedReferral' => auth()->user()->no_of_referral,
            'progression' => $this->getLevelProgress()
        ];
    }

    public function getLevelProgress():int
    {
        if ($this->getCompletedStatus()){
            return 100;
        }
        return (auth()->user()->no_of_referral / $this->no_of_ref) * 100;
    }
    public function getCompletedStatus(): bool
    {
        if (auth()->user()->no_of_referral >= $this->no_of_ref){
            return true;
        }
        return false;
    }
}
