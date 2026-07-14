<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestDriveResource\Pages;
use App\Models\TestDrive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class TestDriveResource extends Resource
{
    protected static ?string $model = TestDrive::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Test Drives';

    protected static ?string $navigationGroup = 'Cars';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'test drive';

    protected static ?string $pluralModelLabel = 'test drives';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Info')
                    ->description('Submitted by the customer from the website.')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Booking Info')
                    ->schema([
                        Forms\Components\TextInput::make('car_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('car_id')
                            ->label('Car ID')
                            ->numeric()
                            ->helperText('Auto-resolved from car name if left blank.'),

                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->maxLength(4),

                        Forms\Components\DateTimePicker::make('booked_at')
                            ->label('Booked At')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Photo')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Car Photo')
                            ->image()
                            ->disk('public')
                            ->directory('test-drives/photos')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('booked_at', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->disk('public')
                    ->height(48)
                    ->defaultImageUrl(null),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('car_name')
                    ->label('Car')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('booked_at')
                    ->label('Booked At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('booked_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $d) => $q->whereDate('booked_at', '>=', $d))
                            ->when($data['until'], fn ($q, $d) => $q->whereDate('booked_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index'  => Pages\ListTestDrives::route('/'),
            'view'   => Pages\ViewTestDrive::route('/{record}'),
        ];
    }
}
