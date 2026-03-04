<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogoResource\Pages;
use App\Filament\Resources\LogoResource\RelationManagers;
use App\Models\Logo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class LogoResource extends Resource
{
    protected static ?string $model = Logo::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Logos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('path')
                    ->label('Image Path')
                    ->hint('e.g. logo-dark.jpeg or TGworld/...')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Placeholder::make('logo_preview')
                    ->label('Preview')
                    ->content(fn ($record) => $record && $record->path
                        ? new HtmlString(
                            '<img src="/' . ltrim($record->path, '/') . '" '
                            . 'style="max-height:160px;border-radius:8px;margin-top:8px;">'
                        )
                        : new HtmlString('<em>Save first to see preview</em>')
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Logo')
                    ->getStateUsing(fn ($record) => '/' . ltrim($record->path ?? '', '/'))
                    ->height(60)
                    ->width(100),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListLogos::route('/'),
            'create' => Pages\CreateLogo::route('/create'),
            'edit'   => Pages\EditLogo::route('/{record}/edit'),
        ];
    }
}
