<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\Worker;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('employee_number')->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone')->tel(),
            Forms\Components\Select::make('team_id')->relationship('team', 'name')->searchable()->preload(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('employee_number')->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('team.name')->sortable(),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([Tables\Filters\SelectFilter::make('team_id')->relationship('team', 'name')->label('Team')])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => WorkerResource\Pages\ManageWorkers::route('/')];
    }
}
