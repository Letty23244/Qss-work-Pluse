<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationLabel = 'Users & Roles';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')->password()->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('Leave blank to keep the current password.'),
            Forms\Components\TextInput::make('phone')->tel(),
            Forms\Components\Select::make('role')->options(User::ROLES)->required()->default('supervisor')->live(),
            Forms\Components\Select::make('client_id')->label('Client company')
                ->relationship('client', 'name')->searchable()->preload()
                ->visible(fn (Forms\Get $get) => $get('role') === 'client')
                ->required(fn (Forms\Get $get) => $get('role') === 'client'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('role')->badge()->formatStateUsing(fn (string $state): string => User::ROLES[$state] ?? $state),
            Tables\Columns\TextColumn::make('client.name'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([Tables\Filters\SelectFilter::make('role')->options(User::ROLES)])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => UserResource\Pages\ManageUsers::route('/')];
    }
}
