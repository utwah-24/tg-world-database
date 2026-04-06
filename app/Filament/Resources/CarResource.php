<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Models\Car;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Carbon;

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

                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue((int) date('Y') + 1)
                    ->placeholder('e.g. 2024')
                    ->default(null),

                Forms\Components\TextInput::make('car_price')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\Select::make('type')
                    ->options([
                        'truck'       => 'Truck',
                        'suv'         => 'SUV',
                        'third_party' => 'Third Party',
                        'sedan'       => 'Sedan',
                        'van'         => 'Van',
                        'pickup'      => 'Pickup',
                    ])
                    ->searchable(),

                Forms\Components\Select::make('condition')
                    ->options([
                        'new'         => 'New',
                        'second_hand' => 'Second Hand',
                        'third_party' => 'Third Party',
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('company')
                    ->maxLength(255)
                    ->placeholder('e.g. Toyota, Ford, BMW')
                    ->default(null),

                Forms\Components\TextInput::make('brand')
                    ->maxLength(255)
                    ->placeholder('e.g. Landcruiser, Ranger, X3')
                    ->default(null),

                Forms\Components\Textarea::make('car_description')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold for Now')
                    ->helperText('Mark this car as sold. You can unmark it at any time.')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_coming_soon')
                    ->label('Coming Soon')
                    ->helperText('Enable to mark this car as arriving soon. Status is auto-removed on the arrival date.')
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('arrival_date')
                    ->label('Arrival Date')
                    ->helperText('The date this car arrives. Coming Soon status is removed automatically on this date.')
                    ->minDate(Carbon::tomorrow())
                    ->native(false)
                    ->visible(fn (Get $get): bool => (bool) $get('is_coming_soon'))
                    ->required(fn (Get $get): bool => (bool) $get('is_coming_soon'))
                    ->columnSpanFull(),

                // ── Existing photos (view + delete) ───────────────────────────
                Forms\Components\Section::make('Current Photos')
                    ->description('Remove photos by clicking the trash icon on each one.')
                    ->schema([
                        Forms\Components\Repeater::make('car_pic_existing')
                            ->label('')
                            ->schema([
                                Forms\Components\Placeholder::make('preview')
                                    ->label('')
                    ->content(fn (Get $get): HtmlString => new HtmlString(
                        $get('path')
                            ? '<img src="'
                                . request()->getSchemeAndHttpHost()
                                . '/'
                                . implode('/', array_map('rawurlencode', explode('/', ltrim($get('path'), '/'))))
                                . '" style="height:120px;width:160px;object-fit:cover;border-radius:6px;">'
                            : '<em style="color:#aaa;">No preview</em>'
                    )),
                                Forms\Components\Hidden::make('path'),
                            ])
                            ->addable(false)
                            ->reorderable()
                            ->grid(4)
                            ->defaultItems(0),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // ── Upload new photos ─────────────────────────────────────────
                Forms\Components\Section::make('Upload New Photos')
                    ->description('Upload additional images. They will be added to the existing ones.')
                    ->schema([
                        Forms\Components\FileUpload::make('car_pic_new')
                            ->label('')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public_root')
                            ->directory('TGworld/uploads')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('car_pic_urls')
                    ->label('Photos')
                    ->stacked()
                    ->limit(3)
                    ->height(60)
                    ->width(80),

                Tables\Columns\TextColumn::make('car_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('car_price')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'truck'       => 'Truck',
                        'suv'         => 'SUV',
                        'third_party' => 'Third Party',
                        'sedan'       => 'Sedan',
                        'van'         => 'Van',
                        'pickup'      => 'Pickup',
                        default       => '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'truck'       => 'warning',
                        'suv'         => 'success',
                        'third_party' => 'info',
                        'sedan'       => 'primary',
                        'van'         => 'danger',
                        'pickup'      => 'gray',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'new'         => 'New',
                        'second_hand' => 'Second Hand',
                        'third_party' => 'Third Party',
                        default       => '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'new'         => 'success',
                        'second_hand' => 'warning',
                        'third_party' => 'info',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('is_sold')
                    ->label('Sold for Now')
                    ->badge()
                    ->state(fn (Car $record): string => $record->is_sold === 'sold' ? 'Sold' : 'Available')
                    ->color(fn (Car $record): string => $record->is_sold === 'sold' ? 'danger' : 'success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Coming Soon')
                    ->badge()
                    ->color('warning')
                    ->state(function (Car $record): ?string {
                        if ($record->is_coming_soon !== 'set') {
                            return null;
                        }

                        return $record->arrival_date
                            ? '🕐 Arrives ' . Carbon::parse($record->arrival_date)->format('d M Y')
                            : 'Coming Soon';
                    })
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

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
                        'sedan'       => 'Sedan',
                        'van'         => 'Van',
                        'pickup'      => 'Pickup',
                    ]),

                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'new'         => 'New',
                        'second_hand' => 'Second Hand',
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
