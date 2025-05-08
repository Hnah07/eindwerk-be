<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConcertResource\Pages;
use App\Filament\Resources\ConcertResource\RelationManagers;
use App\Models\Concert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConcertResource extends Resource
{
    protected static ?string $model = Concert::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('year')
                    ->required()
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue(2100),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'concert' => 'Concert',
                        'festival' => 'Festival',
                        'dj set' => 'DJ Set',
                        'club show' => 'Club Show',
                        'theater show' => 'Theater Show',
                    ]),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'concert' => 'Concert',
                        'festival' => 'Festival',
                        'dj set' => 'DJ Set',
                        'club show' => 'Club Show',
                        'theater show' => 'Theater Show',
                    ]),
                Tables\Filters\Filter::make('year')
                    ->form([
                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['year'],
                                fn(Builder $query, $year): Builder => $query->where('year', $year),
                            );
                    })
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
            'index' => Pages\ListConcerts::route('/'),
            'create' => Pages\CreateConcert::route('/create'),
            'edit' => Pages\EditConcert::route('/{record}/edit'),
        ];
    }
}
