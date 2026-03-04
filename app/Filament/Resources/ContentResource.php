<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('content_name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('content_video')
                    ->label('Video Path')
                    ->hint('e.g. TGworld/content/videoname.mp4')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('duration')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\Placeholder::make('video_preview')
                    ->label('Video Preview')
                    ->content(fn ($record) => $record && $record->content_video
                        ? new HtmlString(
                            '<video controls style="max-width:100%;max-height:320px;'
                            . 'border-radius:8px;margin-top:8px;">'
                            . '<source src="/' . ltrim($record->content_video, '/') . '" type="video/mp4">'
                            . 'Your browser does not support the video tag.'
                            . '</video>'
                        )
                        : new HtmlString('<em>Save the record first to preview the video</em>')
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('content_video')
                    ->label('Video')
                    ->formatStateUsing(fn ($state) => basename($state ?? ''))
                    ->url(fn ($record) => '/' . ltrim($record->content_video ?? '', '/'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-play-circle')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('duration')
                    ->searchable(),

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
            'index'  => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit'   => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
