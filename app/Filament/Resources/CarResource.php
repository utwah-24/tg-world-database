<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Company;
use App\Models\VehicleModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class CarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Cars';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['company', 'brand', 'vehicleModel']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('car_name')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: 'cars',
                        column: 'car_name',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Get $get): Unique {
                            $year = $get('year');

                            return $year === null || $year === ''
                                ? $rule->whereNull('year')
                                : $rule->where('year', $year);
                        },
                    ),

                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue((int) date('Y') + 1)
                    ->placeholder('e.g. 2024')
                    ->default(null),

                Forms\Components\TextInput::make('car_price')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->placeholder('e.g. Truck, SUV, Crossover SUV…')
                    ->datalist([
                        'truck' => 'Truck',
                        'suv' => 'SUV',
                        'sedan' => 'Sedan',
                        'van' => 'Van',
                        'pickup' => 'Pickup',
                        'crossover suv' => 'Crossover SUV',
                    ])
                    ->helperText('Pick a suggestion or type any vehicle type. Stored as plain text (VARCHAR) — matches your database column.')
                    ->maxLength(255),

                Forms\Components\Select::make('condition')
                    ->options([
                        'new' => 'New',
                        'second_hand' => 'Second Hand',
                        'third_party' => 'Third Party',
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('company_name')
                    ->label('Company')
                    ->placeholder('Type company name…')
                    ->datalist(fn (): array => Company::orderBy('name')->pluck('name')->toArray())
                    ->maxLength(255)
                    ->live(debounce: 400)
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $name = trim((string) $state);
                        if ($name === '') {
                            $set('company_logo', null);

                            return;
                        }
                        // Existing company → use saved logo in preview; clear upload so FilePond does not fight it
                        if (Company::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
                            $set('company_logo', null);
                        }
                    }),

                Forms\Components\Placeholder::make('company_logo_preview')
                    ->label('Company logo preview')
                    ->content(function (Get $get): HtmlString {
                        $name = trim((string) ($get('company_name') ?? ''));
                        if ($name === '') {
                            return new HtmlString(
                                '<em style="color:#aaa;">Type a company name to load its logo from the directory, or upload one below for a new company.</em>'
                            );
                        }

                        $company = Company::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

                        if (! $company) {
                            return new HtmlString(
                                '<em style="color:#aaa;">New company — add a logo below if you want one.</em>'
                            );
                        }

                        if (! $company->logo) {
                            return new HtmlString(
                                '<em style="color:#aaa;">This company has no logo on file yet — upload below.</em>'
                            );
                        }

                        return new HtmlString(
                            '<img src="'.e(Car::mediaUrl($company->logo)).'" alt="" style="height:80px;max-width:240px;object-fit:contain;border-radius:4px;">'
                        );
                    }),

                Forms\Components\FileUpload::make('company_logo')
                    ->label('Upload / Change Company Logo')
                    ->helperText('For an existing company, the logo above fills automatically. Upload here only to add or replace the logo.')
                    ->image()
                    ->disk('public_root')
                    ->directory('TGworld/logos')
                    ->imagePreviewHeight('80'),

                Forms\Components\Hidden::make('company_id'),

                Forms\Components\TextInput::make('brand_name')
                    ->label('Brand')
                    ->placeholder('Type or pick from suggestions…')
                    ->datalist(fn (): array => Brand::orderBy('name')->pluck('name')->toArray())
                    ->maxLength(255)
                    ->live(debounce: 400)
                    ->afterStateUpdated(function (Set $set): void {
                        $set('model_name', null);
                    })
                    ->helperText('Creates the brand on first save. Changing brand clears the model field.'),

                Forms\Components\TextInput::make('model_name')
                    ->label('Model')
                    ->placeholder('Type or pick from suggestions…')
                    ->datalist(function (Get $get): array {
                        $brandName = trim((string) ($get('brand_name') ?? ''));
                        if ($brandName === '') {
                            return [];
                        }

                        $brand = Brand::whereRaw('LOWER(name) = ?', [strtolower($brandName)])->first();

                        if (! $brand) {
                            return [];
                        }

                        return VehicleModel::query()
                            ->where('brand_id', $brand->id)
                            ->orderBy('name')
                            ->pluck('name')
                            ->toArray();
                    })
                    ->maxLength(255)
                    ->helperText('Suggestions only include models for the brand above. New names are created for that brand when you save.'),

                Forms\Components\Textarea::make('car_description')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('registration')
                    ->label('Registered')
                    ->helperText('Toggle on if this car is registered, off if unregistered.')
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
                                            ? '<img src="'.e(Car::mediaUrl($get('path'))).'" style="height:120px;width:160px;object-fit:cover;border-radius:6px;">'
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
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession(false)
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
                    ->formatStateUsing(fn ($state) => match (strtolower((string) $state)) {
                        'truck' => 'Truck',
                        'suv' => 'SUV',
                        'third_party' => 'Third Party',
                        'sedan' => 'Sedan',
                        'van' => 'Van',
                        'pickup' => 'Pickup',
                        default => ucwords(str_replace('_', ' ', (string) $state)),
                    })
                    ->weight(fn ($state) => self::isCustomVehicleType($state) ? FontWeight::Bold : null)
                    ->color(function ($state) {
                        if (self::isCustomVehicleType($state)) {
                            return 'info';
                        }

                        return match (strtolower((string) $state)) {
                            'truck' => 'warning',
                            'suv' => 'success',
                            'third_party' => 'info',
                            'sedan' => 'primary',
                            'van' => 'danger',
                            'pickup' => 'gray',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'new' => 'New',
                        'second_hand' => 'Second Hand',
                        'third_party' => 'Third Party',
                        default => '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'new' => 'success',
                        'second_hand' => 'warning',
                        'third_party' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('registration')
                    ->label('Registration')
                    ->badge()
                    ->state(fn (Car $record): string => $record->registration === 'registered' ? 'Registered' : 'Unregistered')
                    ->color(fn (Car $record): string => $record->registration === 'registered' ? 'success' : 'gray')
                    ->toggleable(),

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
                            ? '🕐 Arrives '.Carbon::parse($record->arrival_date)->format('d M Y')
                            : 'Coming Soon';
                    })
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('company_logo_url')
                    ->label('Logo')
                    ->getStateUsing(fn (Car $record): ?string => $record->company_logo_url)
                    ->height(36)
                    ->width(60)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicleModel.name')
                    ->label('Model')
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
                Tables\Filters\Filter::make('type')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Vehicle type')
                            ->placeholder('Contains… e.g. SUV, Crossover'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));

                        return $query->when(
                            $value !== '',
                            fn (Builder $q) => $q->where('type', 'like', '%'.addcslashes($value, '%_\\').'%')
                        );
                    }),

                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'new' => 'New',
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
            'index' => Pages\ListCars::route('/'),
            'create' => Pages\CreateCar::route('/create'),
            'edit' => Pages\EditCar::route('/{record}/edit'),
        ];
    }

    /**
     * True when the stored type is not one of the preset slugs (datalist / legacy enum).
     */
    private static function isCustomVehicleType(mixed $state): bool
    {
        if ($state === null || $state === '') {
            return false;
        }

        $normalized = strtolower(trim((string) $state));

        return ! in_array($normalized, ['truck', 'suv', 'third_party', 'sedan', 'van', 'pickup'], true);
    }
}
