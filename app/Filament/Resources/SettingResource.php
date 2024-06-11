<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('youtube_point'),
                Forms\Components\TextInput::make('twitter_point'),
                Forms\Components\TextInput::make('telegram_point'),
                Forms\Components\TextInput::make('facebook_point'),
                Forms\Components\TextInput::make('referral_level_no'),
                Forms\Components\TextInput::make('daily_point'),
                Forms\Components\TextInput::make('username_point'),
                Forms\Components\TextInput::make('facebook_url'),
                Forms\Components\TextInput::make('twitter_url'),
                Forms\Components\TextInput::make('youtube_url'),
                Forms\Components\TextInput::make('telegram_url')->label('Minning Point'),
                Forms\Components\TextInput::make('ads')->label('Ads Point'),
                Forms\Components\TextInput::make('mining')->label('Minning Point'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('youtube_point'),
                Tables\Columns\TextColumn::make('daily_point'),
                Tables\Columns\TextColumn::make('telegram_point'),
                Tables\Columns\TextColumn::make('twitter_point'),
                Tables\Columns\TextColumn::make('username_point'),
                Tables\Columns\TextColumn::make('facebook_point'),
                Tables\Columns\TextColumn::make('referral_level_no'),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }


}
