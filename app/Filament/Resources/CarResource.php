<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Models\Car;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                        'truck'       => 'Truck',
                        'suv'         => 'SUV',
                        'third_party' => 'Third Party',
                    ])
                    ->searchable(),

                Forms\Components\Textarea::make('car_description')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('car_pic')
                    ->label('Car Photos')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public_root')
                    ->directory('TGworld/cars')
                    ->preserveFilenames()
                    ->appendFiles()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Uses the car_pic_urls accessor on the Car model (same pattern as profile_photo_url)
                Tables\Columns\ImageColumn::make('car_pic_urls')
                    ->label('Photos')
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
                Tables\Actions\ViewAction::make(),
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
