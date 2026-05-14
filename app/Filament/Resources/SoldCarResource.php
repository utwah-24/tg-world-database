<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoldCarResource\Pages;
use App\Models\SoldCar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SoldCarResource extends Resource
{
    protected static ?string $model = SoldCar::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Sold Cars';

    protected static ?string $navigationGroup = 'Cars';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('car_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('car_id')
                    ->label('Car ID (from Cars table)')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('sold_at')
                    ->label('Sold At')
                    ->required(),
                Forms\Components\TextInput::make('price_sold')
                    ->label('Price Sold')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('car_pic_urls')
                    ->label('Photos')
                    ->stacked()
                    ->limit(3)
                    ->height(60)
                    ->width(80),
                Tables\Columns\TextColumn::make('car_id')
                    ->label('Car ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('car_name')
                    ->label('Car Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_sold')
                    ->label('Price Sold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sold_at')
                    ->label('Sold At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_available')
                    ->label('Qty Left')
                    ->sortable()
                    ->placeholder('—')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null   => 'gray',
                        $state === 0      => 'danger',
                        $state <= 2       => 'warning',
                        default           => 'success',
                    }),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->sortable()
                    ->placeholder('—')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sold_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSoldCars::route('/'),
            'create' => Pages\CreateSoldCar::route('/create'),
            'edit'   => Pages\EditSoldCar::route('/{record}/edit'),
        ];
    }
}
