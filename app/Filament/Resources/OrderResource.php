<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Car;
use App\Models\Order;
use App\Models\SoldCar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Info')
                    ->description('Submitted by the customer — read only.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('car_name')
                            ->label('Car Name')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->disabled(),

                        Forms\Components\TextInput::make('email')
                            ->label('Customer Email')
                            ->disabled(),

                        Forms\Components\DatePicker::make('order_date')
                            ->label('Order Date')
                            ->disabled()
                            ->native(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Documents')
                    ->schema([
                        Forms\Components\Placeholder::make('invoice_link')
                            ->label('Invoice')
                            ->content(function ($record): HtmlString {
                                if (! $record || ! filled($record->invoice)) {
                                    return new HtmlString('<em style="color:#aaa;">No invoice uploaded.</em>');
                                }

                                return new HtmlString(
                                    '<a href="'.e(self::fileUrl($record->invoice)).'" target="_blank" rel="noopener noreferrer"
                                        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none;font-size:14px;font-weight:500;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
                                        Open Invoice
                                    </a>'
                                );
                            }),

                        Forms\Components\Placeholder::make('receipt_link')
                            ->label('Receipt')
                            ->content(function ($record): HtmlString {
                                if (! $record || ! filled($record->receipt)) {
                                    return new HtmlString('<em style="color:#aaa;">No receipt uploaded.</em>');
                                }

                                return new HtmlString(
                                    '<a href="'.e(self::fileUrl($record->receipt)).'" target="_blank" rel="noopener noreferrer"
                                        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#16a34a;color:#fff;border-radius:6px;text-decoration:none;font-size:14px;font-weight:500;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
                                        Open Receipt
                                    </a>'
                                );
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Process Order')
                    ->description('Update payment details and approve or reject this order.')
                    ->schema([
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix('Tshs')
                            ->placeholder('0'),

                        Forms\Components\TextInput::make('amount_due')
                            ->label('Amount Due')
                            ->numeric()
                            ->prefix('Tshs')
                            ->placeholder('0'),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('Tshs')
                            ->placeholder('0'),

                        Forms\Components\Toggle::make('status')
                            ->label('Approved')
                            ->helperText('Toggle on = Approved, off = Not Approved')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
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

                Tables\Columns\ImageColumn::make('car_pic_urls')
                    ->label('Photos')
                    ->stacked()
                    ->limit(3)
                    ->height(60)
                    ->width(80),

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

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Tshs '.number_format((float) $state, 0, '.', ',') : null)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('amount_due')
                    ->label('Amount Due')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Tshs '.number_format((float) $state, 0, '.', ',') : null)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Tshs '.number_format((float) $state, 0, '.', ',') : null)
                    ->placeholder('—'),

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

                Tables\Columns\ToggleColumn::make('status')
                    ->label('Approved')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger')
                        ->afterStateUpdated(function (Order $record, bool $state): void {
                        if ($state) {
                            // Approved → add to sold cars (skip if already exists for this order)
                            if (SoldCar::where('order_id', $record->id)->exists()) {
                                return;
                            }

                            $car = $record->car_id
                                ? Car::find($record->car_id)
                                : Car::where('car_name', $record->car_name)->first();

                            SoldCar::create([
                                'order_id'        => $record->id,
                                'car_id'          => $car?->car_id,
                                'car_name'        => $record->car_name,
                                'car_pics'        => $record->car_pics ?? [],
                                'sold_at'         => now(),
                                'price_sold'      => $record->total_amount
                                    ? number_format((float) $record->total_amount, 0, '.', ' ').' Tshs'
                                    : null,
                                'total_available' => $car?->total_available,
                                'qty'             => $record->qty ?? 1,
                            ]);
                        } else {
                            // Unapproved → remove the linked sold car entry
                            SoldCar::where('order_id', $record->id)->delete();
                        }
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Order Details')
                    ->modalWidth('4xl'),
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

        // Use the public disk URL config (APP_URL . '/storage') so the path is
        // correct regardless of whether APP_URL already includes '/public' (cPanel)
        // or not (standard Laravel setup). Hardcoding '/public/storage/' here
        // would produce a double '/public/public/' on cPanel hosts.
        return Storage::disk('public')->url($path);
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
