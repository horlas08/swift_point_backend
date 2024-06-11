<?php


use App\Models\Setting;
use App\Models\Transaction;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

function getTrx(): string{
    return (md5(time()));
}

function makeTransation(string $user_id, string $trx, string $type, int $amount, string $remark)
{
    $transation = new Transaction();
    $transation->user_id = $user_id;
    $transation->trx = $trx;
    $transation->remark = $remark;
    $transation->type = $type;
    $transation->amount = $amount;
    $transation->save();
}

/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function gs(): Setting
{
//    if (cache()->get('settings')){
//        return cache()->get('settings');
//    }
//    cache()->put('settings', Setting::first());
    return Setting::first();
}
