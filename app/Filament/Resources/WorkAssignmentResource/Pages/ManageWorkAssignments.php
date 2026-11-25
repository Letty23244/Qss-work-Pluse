<?php

namespace App\Filament\Resources\WorkAssignmentResource\Pages;

use App\Filament\Resources\WorkAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWorkAssignments extends ManageRecords
{
    protected static string $resource = WorkAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
