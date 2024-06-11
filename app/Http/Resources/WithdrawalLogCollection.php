<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin  \App\Models\WithdrawalLog */
class WithdrawalLogCollection extends JsonResource
{
    public static $wrap = '';
    public function toArray(Request $request): array
    {
        return [
            'trx' => $this->trx,
            'amount' => $this->amount,
            'wallet' => $this->wallet,
            'status' => $this->status,
            'date' => Carbon::parse($this->updated_at)->unix()
        ];
    }
}
