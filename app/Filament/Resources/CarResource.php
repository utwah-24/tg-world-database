<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Company;
use App\Models\VehicleModel;
use App\Services\ComingSoonService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
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
        ComingSoonService::expireDueCars();

        return parent::getEloquentQuery()
            ->with(['company', 'brand', 'vehicleModel'])
            ->orderBy('created_at', 'desc');
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
                    ->label('Car price')
                    ->suffix('Million Tshs')
                    ->placeholder('e.g. 295 or 28.5')
                    ->maxLength(16)
                    ->extraInputAttributes(['inputmode' => 'decimal', 'pattern' => '[0-9]*[.]?[0-9]*', 'autocomplete' => 'off'])
                    ->rules(['nullable', 'regex:/^(\d+(\.\d+)?)?$/'])
                    ->helperText('Millions of Tshs — whole number or decimal. “Million Tshs” is added when you save.'),

                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->placeholder('e.g. Truck, SUV, Crossover SUV…')
                    ->datalist([
                        'bus'             => 'Bus',
                        'convertible'     => 'Convertible',
                        'coupe'           => 'Coupe',
                        'crossover suv'   => 'Crossover SUV',
                        'hatchback'       => 'Hatchback',
                        'heavy equipment' => 'Heavy Equipment',
                        'minivan'         => 'Minivan',
                        'pickup'          => 'Pickup',
                        'sedan'           => 'Sedan',
                        'station wagon'   => 'Station Wagon',
                        'suv'             => 'SUV',
                        'truck'           => 'Truck',
                        'van'             => 'Van',
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

                Forms\Components\TextInput::make('color')
                    ->label('Color')
                    ->maxLength(255)
                    ->placeholder('e.g. Pearl White, Midnight Black')
                    ->default(null),

                Forms\Components\TextInput::make('chassis')
                    ->label('Chasis')
                    ->maxLength(255)
                    ->placeholder('e.g. VIN / chassis number')
                    ->default(null),

                Forms\Components\TextInput::make('mileage')
                    ->label('Mileage')
                    ->suffix(' km')
                    ->maxLength(12)
                    ->placeholder('e.g. 85000')
                    ->extraInputAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*', 'autocomplete' => 'off'])
                    ->rules(['nullable', 'regex:/^[0-9]*$/'])
                    ->helperText('Digits only — “km” is added when you save.')
                    ->default(null),

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

                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->rows(5)
                    ->helperText('Internal notes or additional details about this car (supports full paragraphs).'),

                Forms\Components\Toggle::make('test_drive_available')
                    ->label('Test drive')
                    ->helperText('Toggle on if this car is available for test drives, off if not.')
                    ->default(false)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('registration')
                    ->label('Registered')
                    ->helperText('Toggle on if this car is registered, off if unregistered.')
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('registration_number')
                    ->label('Registration Number')
                    ->placeholder('e.g. T 123 ABC')
                    ->maxLength(50)
                    ->visible(fn (Get $get): bool => (bool) $get('registration'))
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('in_dar')
                    ->label('In Dar')
                    ->helperText('Toggle on if this car is in Dar es Salaam. Toggle off to enter a custom location.')
                    ->default(true)
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('location')
                    ->label('Location')
                    ->placeholder('e.g. Arusha, Mwanza, Dodoma…')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => ! (bool) $get('in_dar'))
                    ->required(fn (Get $get): bool => ! (bool) $get('in_dar'))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('total_available')
                    ->label('Total Available')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('e.g. 5')
                    ->helperText('Total number of units available for this car.')
                    ->default(null),

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

                Tables\Columns\TextColumn::make('color')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('chassis')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('mileage')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Columns\IconColumn::make('test_drive_available')
                    ->label('Test Drive')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('registration')
                    ->label('Registration')
                    ->badge()
                    ->state(fn (Car $record): string => $record->registration === 'registered' ? 'Registered' : 'Unregistered')
                    ->color(fn (Car $record): string => $record->registration === 'registered' ? 'success' : 'gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Reg. Number')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_available')
                    ->label('Total Available')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('in_dar')
                    ->label('In Dar')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-map-pin')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->placeholder('Dar es Salaam')
                    ->searchable()
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
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(2)
            ->filters([
                Tables\Filters\Filter::make('car_name')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Car name')
                            ->placeholder('Contains…'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }

                        return $query->where('car_name', 'like', '%'.addcslashes($value, '%_\\').'%');
                    }),

                Tables\Filters\Filter::make('year')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('Year from')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue((int) date('Y') + 1),
                        Forms\Components\TextInput::make('until')
                            ->label('Year to')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue((int) date('Y') + 1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $q) => $q->where('year', '>=', (int) $data['from'])
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $q) => $q->where('year', '<=', (int) $data['until'])
                            );
                    }),

                Tables\Filters\Filter::make('car_price')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Price')
                            ->placeholder('Contains… e.g. 295'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }

                        return $query->where('car_price', 'like', '%'.addcslashes($value, '%_\\').'%');
                    }),

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

                Tables\Filters\Filter::make('color')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Color')
                            ->placeholder('Contains…'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }

                        return $query->where('color', 'like', '%'.addcslashes($value, '%_\\').'%');
                    }),

                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('vehicle_model_id')
                    ->label('Model')
                    ->relationship('vehicleModel', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('company_or_label')
                    ->label('Company name (text)')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Matches company or snapshot label')
                            ->placeholder('Contains…'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }
                        $like = '%'.addcslashes($value, '%_\\').'%';

                        return $query->where(function (Builder $q) use ($like): void {
                            $q->whereHas('company', fn (Builder $q2) => $q2->where('name', 'like', $like))
                                ->orWhere('company_label', 'like', $like);
                        });
                    }),

                Tables\Filters\Filter::make('brand_or_label')
                    ->label('Brand name (text)')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Matches brand or snapshot label')
                            ->placeholder('Contains…'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }
                        $like = '%'.addcslashes($value, '%_\\').'%';

                        return $query->where(function (Builder $q) use ($like): void {
                            $q->whereHas('brand', fn (Builder $q2) => $q2->where('name', 'like', $like))
                                ->orWhere('brand_label', 'like', $like);
                        });
                    }),

                Tables\Filters\Filter::make('model_or_label')
                    ->label('Model name (text)')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Matches model or snapshot label')
                            ->placeholder('Contains…'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }
                        $like = '%'.addcslashes($value, '%_\\').'%';

                        return $query->where(function (Builder $q) use ($like): void {
                            $q->whereHas('vehicleModel', fn (Builder $q2) => $q2->where('name', 'like', $like))
                                ->orWhere('model_label', 'like', $like);
                        });
                    }),

                Tables\Filters\Filter::make('car_description')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Description')
                            ->placeholder('Contains…'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['value'] ?? ''));
                        if ($value === '') {
                            return $query;
                        }

                        return $query->where('car_description', 'like', '%'.addcslashes($value, '%_\\').'%');
                    }),

                Tables\Filters\SelectFilter::make('registration')
                    ->options([
                        'registered' => 'Registered',
                        'unregistered' => 'Unregistered',
                    ]),

                Tables\Filters\SelectFilter::make('is_sold')
                    ->label('Availability')
                    ->options([
                        'sold' => 'Sold',
                        'available' => 'Available',
                    ]),

                Tables\Filters\Filter::make('coming_soon')
                    ->label('Coming soon')
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label('Status')
                            ->options([
                                'set' => 'Coming soon only',
                                'not_set' => 'Not coming soon',
                            ])
                            ->placeholder('All'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $v = $data['value'] ?? null;
                        if ($v === 'set') {
                            return $query->where('is_coming_soon', 'set');
                        }
                        if ($v === 'not_set') {
                            return $query->where(function (Builder $q): void {
                                $q->whereNull('is_coming_soon')
                                    ->orWhere('is_coming_soon', '!=', 'set');
                            });
                        }

                        return $query;
                    }),

                Tables\Filters\Filter::make('arrival_date')
                    ->label('Arrival date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Arrives from')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Arrives until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $q) => $q->whereDate('arrival_date', '>=', $data['from'])
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $q) => $q->whereDate('arrival_date', '<=', $data['until'])
                            );
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->label('Added (created)')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Created from')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Created until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $q) => $q->whereDate('created_at', '>=', $data['from'])
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $q) => $q->whereDate('created_at', '<=', $data['until'])
                            );
                    }),

                Tables\Filters\Filter::make('updated_at')
                    ->label('Last updated')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Updated from')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Updated until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $q) => $q->whereDate('updated_at', '>=', $data['from'])
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $q) => $q->whereDate('updated_at', '<=', $data['until'])
                            );
                    }),
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
