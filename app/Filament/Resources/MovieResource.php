<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovieResource\Pages;
use App\Filament\Resources\MovieResource\RelationManagers\CastRelationManager;
use App\Filament\Resources\MovieResource\RelationManagers\CompaniesRelationManager;
use App\Filament\Resources\MovieResource\RelationManagers\CrewRelationManager;
use App\Filament\Resources\MovieResource\RelationManagers\GenresRelationManager;
use App\Filament\Resources\MovieResource\RelationManagers\KeywordsRelationManager;
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

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                Forms\Components\Tabs::make('Movie')->tabs([
                    Forms\Components\Tabs\Tab::make('Description')
                        ->schema([
                            Forms\Components\TextInput::make('title')->required()->maxLength(255),
                            Forms\Components\TextInput::make('tagline')->maxLength(65535),
                            Forms\Components\MarkdownEditor::make('description')->required()->maxLength(65535),
                        ]),
                    Forms\Components\Tabs\Tab::make('Metadata')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('poster')->required()->maxLength(255),
                                    Forms\Components\TextInput::make('budget')->required(),
                                    Forms\Components\TextInput::make('revenue')->required(),
                                    Forms\Components\TextInput::make('runtime')->required(),
                                    Forms\Components\TextInput::make('popularity')->required(),
                                    Forms\Components\TextInput::make('vote_average')->required(),
                                    Forms\Components\TextInput::make('vote_count')->required(),
                                    Forms\Components\TextInput::make('imdb_id')->required()->maxLength(9),
                                    Forms\Components\TextInput::make('homepage')->maxLength(255),
                                    Forms\Components\DatePicker::make('release_date'),
                                ]),
                        ]),
                ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->sortable(),
                Tables\Columns\TextColumn::make('release_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->formatStateUsing(fn ($column, $state) => $state ? number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('revenue')
                    ->formatStateUsing(fn ($column, $state) => $state ? number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('runtime')->sortable(),
                Tables\Columns\TextColumn::make('popularity')->sortable(),
                Tables\Columns\TextColumn::make('vote_average')->sortable(),
                Tables\Columns\TextColumn::make('vote_count')->sortable(),
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
            CastRelationManager::class,
            CrewRelationManager::class,
            CompaniesRelationManager::class,
            GenresRelationManager::class,
            KeywordsRelationManager::class,
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

    public static function getWidgets(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'tagline', 'description', 'keywords.name'];
    }
}
