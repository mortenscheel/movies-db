<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovieResource\Pages;
use App\Models\Movie;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('tagline')
                    ->maxLength(65535),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('poster')
                    ->required()
                    ->maxLength(255),
                // Forms\Components\Toggle::make('adult')
                //     ->required(),
                Forms\Components\TextInput::make('budget')
                    ->required(),
                Forms\Components\TextInput::make('revenue')
                    ->required(),
                Forms\Components\TextInput::make('runtime')
                    ->required(),
                Forms\Components\TextInput::make('popularity')
                    ->required(),
                Forms\Components\TextInput::make('vote_average')
                    ->required(),
                Forms\Components\TextInput::make('vote_count')
                    ->required(),
                Forms\Components\TextInput::make('imdb_id')
                    ->required()
                    ->maxLength(9),
                Forms\Components\TextInput::make('homepage')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('release_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('tagline'),
                // Tables\Columns\TextColumn::make('description'),
                // Tables\Columns\TextColumn::make('poster'),
                // Tables\Columns\IconColumn::make('adult')
                //     ->boolean()->sortable(),
                Tables\Columns\TextColumn::make('budget'),
                Tables\Columns\TextColumn::make('revenue'),
                Tables\Columns\TextColumn::make('runtime'),
                Tables\Columns\TextColumn::make('popularity'),
                Tables\Columns\TextColumn::make('vote_average'),
                Tables\Columns\TextColumn::make('vote_count'),
                // Tables\Columns\TextColumn::make('imdb_id'),
                // Tables\Columns\TextColumn::make('homepage'),
                Tables\Columns\TextColumn::make('release_date')
                    ->date(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMovies::route('/'),
            'create' => Pages\CreateMovie::route('/create'),
            'edit' => Pages\EditMovie::route('/{record}/edit'),
            'view' => Pages\ViewMovie::route('/{record}'),
        ];
    }
}
