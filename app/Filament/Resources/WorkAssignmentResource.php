<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\WorkAssignment;

class WorkAssignmentResource extends Resource
{
    protected static ?string $model = WorkAssignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')->relationship('client', 'name')->required()->searchable()->preload(),
            Forms\Components\Select::make('team_id')->relationship('team', 'name')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\TextInput::make('activity')->required(),
            Forms\Components\TextInput::make('unit')->default('units')->required(),
            Forms\Components\TextInput::make('expected_output')->numeric()->minValue(0)->required(),
            Forms\Components\DatePicker::make('start_date')->required()->default(today()),
            Forms\Components\DatePicker::make('due_date'),
            Forms\Components\Select::make('status')->options(WorkAssignment::STATUSES)->default('pending')->required(),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('start_date', 'desc')->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('client.name')->searchable(),
            Tables\Columns\TextColumn::make('team.name'),
            Tables\Columns\TextColumn::make('activity'),
            Tables\Columns\TextColumn::make('expected_output')->numeric(2),
            Tables\Columns\TextColumn::make('produced')->label('Produced')
                ->state(fn (WorkAssignment $r) => number_format($r->totalProduced(), 2)),
            Tables\Columns\TextColumn::make('status')->badge()
                ->color(fn (string $state): string => ['pending' => 'gray', 'in_progress' => 'warning', 'completed' => 'success'][$state] ?? 'gray'),
            Tables\Columns\TextColumn::make('due_date')->date(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(WorkAssignment::STATUSES),
            Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->label('Client'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => WorkAssignmentResource\Pages\ManageWorkAssignments::route('/')];
    }
}
