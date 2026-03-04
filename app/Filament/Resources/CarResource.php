<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Filament\Resources\CarResource\RelationManagers;
use App\Models\Car;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Cars';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('car_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('car_price')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\Select::make('type')
                    ->options([
                        'truck'        => 'Truck',
                        'suv'          => 'SUV',
                        'third_party'  => 'Third Party',
                    ])
                    ->searchable(),

                Forms\Components\Textarea::make('car_description')
                    ->columnSpanFull(),

                Forms\Components\TagsInput::make('car_pic')
                    ->label('Car Photo Paths')
                    ->hint('Enter each image path (e.g. TGworld/SUV/carname/front.jpeg) and press Enter')
                    ->columnSpanFull(),

                Forms\Components\Placeholder::make('image_preview')
                    ->label('Current Photos')
                    ->content(fn ($record) => $record
                        ? new HtmlString(
                            collect($record->car_pic ?? [])
                                ->map(fn ($path) => '<img src="/' . ltrim($path, '/') . '" '
                                    . 'style="height:120px;width:160px;object-fit:cover;'
                                    . 'margin:4px;border-radius:6px;display:inline-block;">')
                                ->join('')
                            ?: '<em>No photos yet</em>'
                        )
                        : new HtmlString('<em>Save the record first to preview photos</em>')
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('car_pic')
                    ->label('Photo')
                    ->getStateUsing(fn ($record): array =>
                        collect($record->car_pic ?? [])
                            ->map(fn ($path) => '/' . ltrim($path, '/'))
                            ->toArray()
                    )
                    ->stacked()
                    ->limit(3)
                    ->height(60)
                    ->width(80),

                Tables\Columns\TextColumn::make('car_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('car_price')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'truck'       => 'warning',
                        'suv'         => 'success',
                        'third_party' => 'info',
                        default       => 'gray',
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
                        'truck'       => 'Truck',
                        'suv'         => 'SUV',
                        'third_party' => 'Third Party',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCars::route('/'),
            'create' => Pages\CreateCar::route('/create'),
            'edit'   => Pages\EditCar::route('/{record}/edit'),
        ];
    }
}
