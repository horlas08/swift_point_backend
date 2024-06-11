<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Count', User::count())
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
        ];
    }
}
