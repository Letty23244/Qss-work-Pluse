<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\{ProductionRecord, Shift, WorkAssignment};

class ProductionRecordResource extends Resource
{
    protected static ?string $model = ProductionRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('shift_id')->relationship('shift', 'id')
                ->getOptionLabelFromRecordUsing(fn (Shift $s) => $s->label)
                ->required()->searchable()->preload()->disabledOn('edit'),
            Forms\Components\Select::make('work_assignment_id')->label('Work assignment')
                ->relationship('assignment', 'title')->required()->searchable()->preload()->disabledOn('edit'),
            Forms\Components\TextInput::make('quantity_produced')->numeric()->minValue(0)->required(),
            Forms\Components\TextInput::make('quantity_pending')->numeric()->minValue(0)->default(0)->required(),
            Forms\Components\Select::make('status')->options(ProductionRecord::STATUSES)->default('submitted')->required(),
            Forms\Components\Textarea::make('remarks')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('submitted_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('shift_type')->badge(),
            Tables\Columns\TextColumn::make('client.name')->searchable(),
            Tables\Columns\TextColumn::make('team.name'),
            Tables\Columns\TextColumn::make('assignment.title')->label('Assignment'),
            Tables\Columns\TextColumn::make('quantity_produced')->numeric(2)->summarize(Tables\Columns\Summarizers\Sum::make()),
            Tables\Columns\TextColumn::make('quantity_pending')->numeric(2),
            Tables\Columns\TextColumn::make('supervisor.name'),
            Tables\Columns\TextColumn::make('submitted_at')->dateTime(),
            Tables\Columns\TextColumn::make('status')->badge()
                ->color(fn (string $state): string => ['submitted' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$state] ?? 'gray'),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(ProductionRecord::STATUSES),
            Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->label('Client'),
            Tables\Filters\SelectFilter::make('team_id')->relationship('team', 'name')->label('Team'),
        ])->actions([
            Tables\Actions\Action::make('approve')->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()
                ->visible(fn (ProductionRecord $r) => $r->status !== 'approved')
                ->action(fn (ProductionRecord $r) => $r->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()])),
            Tables\Actions\Action::make('reject')->icon('heroicon-o-x-circle')->color('danger')->requiresConfirmation()
                ->visible(fn (ProductionRecord $r) => $r->status === 'submitted')
                ->action(fn (ProductionRecord $r) => $r->update(['status' => 'rejected', 'approved_by' => auth()->id(), 'approved_at' => now()])),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ProductionRecordResource\Pages\ManageProductionRecords::route('/')];
    }
}
