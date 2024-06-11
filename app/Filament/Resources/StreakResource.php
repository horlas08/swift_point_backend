<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StreakResource\Pages;
use App\Filament\Resources\StreakResource\RelationManagers;
use App\Models\Streak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StreakResource extends Resource
{
    protected static ?string $model = Streak::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('point')->numeric()->step(0.1),
                Forms\Components\TextInput::make('days')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
//                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('days'),
                Tables\Columns\TextColumn::make('point')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStreaks::route('/'),
            'create' => Pages\CreateStreak::route('/create'),
            'edit' => Pages\EditStreak::route('/{record}/edit'),
        ];
    }
}
