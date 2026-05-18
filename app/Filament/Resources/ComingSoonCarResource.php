<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComingSoonCarResource\Pages;
use App\Models\Car;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComingSoonCarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Coming Soon';

    protected static ?string $navigationGroup = 'Cars';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'coming-soon';

    protected static ?string $modelLabel = 'coming soon car';

    protected static ?string $pluralModelLabel = 'coming soon cars';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['company', 'brand', 'vehicleModel'])
            ->where('is_coming_soon', 'set')
            ->orderBy('arrival_date')
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return CarResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return CarResource::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComingSoonCars::route('/'),
            'create' => Pages\CreateComingSoonCar::route('/create'),
            'edit' => Pages\EditComingSoonCar::route('/{record}/edit'),
        ];
    }
}
