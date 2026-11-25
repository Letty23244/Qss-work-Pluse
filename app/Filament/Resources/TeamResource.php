<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\Team;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('client_id')->relationship('client', 'name')->searchable()->preload(),
            Forms\Components\Select::make('supervisor_id')->label('Supervisor')
                ->relationship('supervisor', 'name', fn ($q) => $q->where('role', 'supervisor'))->searchable()->preload(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('client.name')->sortable(),
            Tables\Columns\TextColumn::make('supervisor.name'),
            Tables\Columns\TextColumn::make('workers_count')->counts('workers')->label('Workers'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->label('Client')])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => TeamResource\Pages\ManageTeams::route('/')];
    }
}
