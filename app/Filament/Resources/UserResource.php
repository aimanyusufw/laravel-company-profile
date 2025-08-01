<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('email_verified_at')
                        ->native(false)
                        ->placeholder("mm / dd / yyyy")
                        ->suffixIcon("heroicon-o-calendar"),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->minLength(8)
                        ->revealable()
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                        ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                        ->dehydrated(fn($state) => filled($state))
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('confirm_password')
                        ->password()
                        ->minLength(8)
                        ->required(
                            fn($livewire) =>
                            $livewire instanceof Pages\CreateUser ||
                                ($livewire instanceof Pages\EditUser && filled($livewire->data['password']))
                        )
                        ->same('password')
                        ->revealable()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Grid::make()->schema([
                    Forms\Components\Section::make("Authority")
                        ->description("details about the authority")
                        ->schema([
                            Forms\Components\Select::make('roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                        ]),
                    Forms\Components\Section::make("Time Stamps")
                        ->description("details of when data was changed and also created")
                        ->schema([
                            Forms\Components\Placeholder::make("created_at")
                                ->content(fn(?User $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                            Forms\Components\Placeholder::make("updated_at")
                                ->content(fn(?User $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                        ])
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('id', '!=', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->default("User has no role")
                    ->colors([
                        'danger' => 'User has no role'
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateDescription('Create user and detail data.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create user')
                    ->url(UserResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');;
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
