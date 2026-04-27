<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('order_date')
                    ->label('Order Date')
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('car_name')
                    ->label('Car Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('year')
                    ->label('Year')
                    ->maxLength(4)
                    ->placeholder('e.g. 2024'),

                Forms\Components\FileUpload::make('invoice')
                    ->label('Invoice (PDF)')
                    ->disk('public')
                    ->directory('orders/invoices')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable()
                    ->openable()
                    ->helperText('Upload the invoice PDF for this order.')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('receipt')
                    ->label('Receipt')
                    ->disk('public')
                    ->directory('orders/receipts')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic'])
                    ->downloadable()
                    ->openable()
                    ->helperText('Upload the receipt — PDF or any image (JPG, PNG, WEBP, HEIC).')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('status')
                    ->label('Status')
                    ->helperText('Toggle on = Approved, off = Not Approved')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('car_name')
                    ->label('Car Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Year')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('invoice')
                    ->label('Invoice')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn (Order $record): bool => filled($record->invoice))
                    ->url(fn (Order $record): ?string => filled($record->invoice) ? self::fileUrl($record->invoice) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\IconColumn::make('receipt')
                    ->label('Receipt')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn (Order $record): bool => filled($record->receipt))
                    ->url(fn (Order $record): ?string => filled($record->receipt) ? self::fileUrl($record->receipt) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\ToggleColumn::make('status')
                    ->label('Status')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function fileUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
