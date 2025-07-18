<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Filament\Resources\TestimonialResource\RelationManagers;
use App\Models\Testimonial;
use App\Settings\AppSetting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    CuratorPicker::make("thumbnail_id")
                        ->label("Thumbnail")
                        ->helperText("Testimonial thumbnail image")
                        ->relationship("thumbnail", "path"),
                    CuratorPicker::make("avatar_id")
                        ->label("Avatar")
                        ->helperText("User avatar image")
                        ->relationship("avatar", "path"),
                    Forms\Components\TextInput::make('client_name')
                        ->label("Client Name")
                        ->helperText("The name of client in the project")
                        ->placeholder("Jhon Doe")
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('client_occupation')
                        ->label("Client Occupation")
                        ->helperText("The occupation of client in the project")
                        ->placeholder("Director")
                        ->maxLength(255),
                    Forms\Components\Textarea::make('messages')
                        ->rows(5)
                        ->label("Message")
                        ->helperText("Message given by client")
                        ->placeholder("This is best project")
                        ->required()
                        ->maxLength(255),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps & Detail")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Select::make('language')
                            ->helperText("The language of this testimonials")
                            ->placeholder("Indonesia")
                            ->required()
                            ->options(function () {
                                $languages = app(AppSetting::class)->languages;

                                return collect($languages)
                                    ->mapWithKeys(fn($lang) => [$lang['iso_code'] => $lang['name']])
                                    ->toArray();
                            })
                            ->native(false)
                            ->default("ID"),
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?Testimonial $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?Testimonial $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail.path')
                    ->default("https://cdn-icons-png.flaticon.com/512/13434/13434972.png")
                    ->sortable(),
                Tables\Columns\ImageColumn::make('avatar.path')
                    ->circular()
                    ->default("https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541")
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('messages')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_occupation')
                    ->placeholder("Occupation is null")
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon('heroicon-o-chat-bubble-bottom-center-text')
            ->emptyStateDescription('Create new testimonial data here.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
