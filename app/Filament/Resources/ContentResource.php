<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
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

                Forms\Components\TextInput::make('duration')
                    ->maxLength(255)
                    ->default(null),

                // Same pattern as profile_photo_path — stores path, disk handles the file
                Forms\Components\FileUpload::make('content_video')
                    ->label('Video File')
                    ->acceptedFileTypes(['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'])
                    ->disk('public_root')
                    ->directory('TGworld/content')
                    ->preserveFilenames()
                    ->columnSpanFull(),

                // Inline video preview (uses the video_url accessor, same idea as profile_photo_url)
                Forms\Components\Placeholder::make('video_preview')
                    ->label('Current Video Preview')
                    ->content(fn ($record) => $record?->video_url
                        ? new HtmlString(
                            '<video controls style="max-width:100%;max-height:300px;border-radius:8px;">'
                            . '<source src="' . $record->video_url . '" type="video/mp4">'
                            . '</video>'
                        )
                        : new HtmlString('<em style="color:#888;">No video uploaded yet</em>')
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

                // Uses the video_url accessor on the Content model (same pattern as profile_photo_url)
                Tables\Columns\TextColumn::make('video_url')
                    ->label('Video')
                    ->formatStateUsing(fn ($record) => basename($record->content_video ?? ''))
                    ->url(fn ($record) => $record->video_url)
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
            'index'  => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit'   => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
