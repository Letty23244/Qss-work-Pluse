<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\{Shift, Target};

class TargetResource extends Resource
{
    protected static ?string $model = Target::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_assignment_id')->label('Work assignment')
                ->relationship('assignment', 'title')->required()->searchable()->preload(),
            Forms\Components\DatePicker::make('date')->required()->default(today()),
            Forms\Components\Select::make('shift_type')->options(Shift::TYPES)
                ->placeholder('Whole day')->helperText('Leave empty for a whole-day target; pick a shift to allocate part of the day target.'),
            Forms\Components\TextInput::make('quantity')->numeric()->minValue(0)->required(),
            Forms\Components\Textarea::make('last_adjustment_reason')->label('Reason for change')
                ->helperText('Recorded in the adjustment history when the quantity changes.')->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('assignment.title')->label('Assignment')->searchable(),
            Tables\Columns\TextColumn::make('client.name'),
            Tables\Columns\TextColumn::make('shift_type')->placeholder('Whole day')->badge(),
            Tables\Columns\TextColumn::make('quantity')->numeric(2),
            Tables\Columns\TextColumn::make('unit'),
            Tables\Columns\TextColumn::make('adjustments_count')->counts('adjustments')->label('Adjustments'),
        ])->filters([Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->label('Client')])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => TargetResource\Pages\ManageTargets::route('/')];
    }
}
