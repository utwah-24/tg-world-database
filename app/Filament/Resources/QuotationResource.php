<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Quotes';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request')->schema([
                Forms\Components\TextInput::make('reference')->disabled(),
                Forms\Components\TextInput::make('full_name')->label('Customer')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\TextInput::make('phone')->disabled(),
                Forms\Components\TextInput::make('car_id')->label('Car ID')->disabled(),
                Forms\Components\TextInput::make('proposed_price')->label('Proposed price')->prefix('TZS')->disabled(),
                Forms\Components\Textarea::make('customer_notes')->disabled()->columnSpanFull(),
                Forms\Components\Placeholder::make('vehicle_details')->content(fn (?Quotation $record) => $record ? json_encode($record->vehicle_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—')->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Staff review')->schema([
                Forms\Components\Select::make('status')->options(array_combine(Quotation::STATUSES, array_map('ucfirst', Quotation::STATUSES)))->required(),
                Forms\Components\TextInput::make('counter_price')->numeric()->minValue(1)->prefix('TZS')->nullable(),
                Forms\Components\Textarea::make('staff_notes')->label('Internal staff notes')->helperText('Never shown to the customer.')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('reference')->searchable()->sortable()->copyable(),
            Tables\Columns\TextColumn::make('customer.username')->label('Account')->searchable(),
            Tables\Columns\TextColumn::make('full_name')->label('Customer')->searchable(),
            Tables\Columns\TextColumn::make('vehicle_snapshot.title')->label('Car')->searchable(),
            Tables\Columns\TextColumn::make('proposed_price')->label('Offer')->formatStateUsing(fn ($state) => 'TZS '.number_format((int) $state))->sortable(),
            Tables\Columns\TextColumn::make('counter_price')->label('Counter')->formatStateUsing(fn ($state) => $state ? 'TZS '.number_format((int) $state) : '—'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                'accepted' => 'success',
                'rejected', 'expired' => 'danger',
                'countered' => 'warning',
                'withdrawn' => 'gray',
                default => 'info',
            })->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Submitted')->dateTime('d M Y, H:i')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(array_combine(Quotation::STATUSES, array_map('ucfirst', Quotation::STATUSES))),
            Tables\Filters\Filter::make('created_at')->form([
                Forms\Components\DatePicker::make('from'),
                Forms\Components\DatePicker::make('until'),
            ])->query(fn ($query, array $data) => $query
                ->when($data['from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                ->when($data['until'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date))),
        ])->actions([
            Tables\Actions\Action::make('preview')->label('PDF')->icon('heroicon-o-document-arrow-down')->action(function (Quotation $record) {
                abort_unless($record->preview_pdf_path && Storage::disk('local')->exists($record->preview_pdf_path), 404);

                return Storage::disk('local')->download($record->preview_pdf_path, "{$record->reference}.pdf");
            }),
            Tables\Actions\EditAction::make()->modalHeading('Review quotation')->modalWidth('5xl'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListQuotations::route('/')];
    }
}
