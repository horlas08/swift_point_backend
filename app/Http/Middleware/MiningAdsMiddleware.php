<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class MiningAdsMiddleware
{
    /**
     * @throws ApiException
     */
    public function handle(Request $request, Closure $next)
    {
        if (Carbon::parse($request->user()->last_mining)->diffInSeconds(now()) >= 86400){
//            throw new ApiException(Carbon::parse($request->user()->last_mining)->diffInSeconds(now()));

            return $next($request);
        }
        throw new ApiException(Carbon::parse($request->user()->last_mining)->diffInSeconds(now()));

    }
}
