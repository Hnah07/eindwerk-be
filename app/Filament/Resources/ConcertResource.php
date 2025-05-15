<?php

namespace App\Filament\Resources;

use App\Enums\ConcertSource;
use App\Enums\ConcertStatus;
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
                Forms\Components\Select::make('source')
                    ->required()
                    ->options([
                        ConcertSource::MANUAL->value => 'Manual',
                        ConcertSource::API->value => 'API',
                    ]),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        ConcertStatus::PENDING_APPROVAL->value => 'Pending Approval',
                        ConcertStatus::VERIFIED->value => 'Verified',
                        ConcertStatus::REJECTED->value => 'Rejected',
                    ]),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Repeater::make('occurrences')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('street')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('housenr')
                                    ->required()
                                    ->maxLength(10),
                                Forms\Components\TextInput::make('zipcode')
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('country')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('longitude')
                                    ->required()
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180),
                                Forms\Components\TextInput::make('latitude')
                                    ->required()
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90),
                            ]),
                        Forms\Components\DatePicker::make('date')
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn(ConcertSource $state): string => match ($state) {
                        ConcertSource::MANUAL => 'gray',
                        ConcertSource::API => 'success',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(ConcertStatus $state): string => match ($state) {
                        ConcertStatus::PENDING_APPROVAL => 'warning',
                        ConcertStatus::VERIFIED => 'success',
                        ConcertStatus::REJECTED => 'danger',
                    }),
                Tables\Columns\TextColumn::make('locations.name')
                    ->label('Location')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->locations->count()) {
                            return '-';
                        }

                        $totalLocations = $record->locations->count();
                        $firstLocation = $record->locations->first()->name;

                        if ($totalLocations === 1) {
                            return $firstLocation;
                        }

                        return $firstLocation . ' (+' . ($totalLocations - 1) . ' more)';
                    })
                    ->tooltip(function ($record) {
                        if (!$record->locations->count()) {
                            return null;
                        }

                        return $record->locations
                            ->pluck('name')
                            ->join("\n");
                    }),
                Tables\Columns\TextColumn::make('occurrences.date')
                    ->label('Dates')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->occurrences->count()) {
                            return '-';
                        }

                        $firstDate = $record->occurrences
                            ->sortBy('date')
                            ->first();

                        $totalDates = $record->occurrences->count();

                        return date('d/m/Y', strtotime($firstDate->date)) .
                            ' (' . $totalDates . ' ' . str('date')->plural($totalDates) . ')';
                    })
                    ->tooltip(function ($record) {
                        if (!$record->occurrences->count()) {
                            return null;
                        }

                        return $record->occurrences
                            ->sortBy('date')
                            ->map(fn($occurrence) => date('d/m/Y', strtotime($occurrence->date)))
                            ->join("\n");
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'concert' => 'Concert',
                        'festival' => 'Festival',
                        'dj set' => 'DJ Set',
                        'club show' => 'Club Show',
                        'theater show' => 'Theater Show',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        ConcertSource::MANUAL->value => 'Manual',
                        ConcertSource::API->value => 'API',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ConcertStatus::PENDING_APPROVAL->value => 'Pending Approval',
                        ConcertStatus::VERIFIED->value => 'Verified',
                        ConcertStatus::REJECTED->value => 'Rejected',
                    ])
                    ->multiple(),
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
                    }),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereHas('occurrences', fn($q) => $q->where('date', '>=', $date))
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereHas('occurrences', fn($q) => $q->where('date', '<=', $date))
                            );
                    }),
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('locations', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(false),
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
