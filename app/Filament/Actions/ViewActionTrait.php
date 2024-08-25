<?php

namespace App\Filament\Actions;

use App\Models\Request;

trait ViewActionTrait
{
    protected function setUp(): void
    {
        $this->name ??= 'view-actions';

        $this->modalSubmitAction(false);

        $this->color('primary');

        $this->label('View Logs');

        $this->icon('heroicon-s-folder');

        $this->slideOver();

        $this->modalContent(function (Request $record) {
            $relatedRecords = $record->actions()->orderByRaw('time DESC')->get();
            $actionStatuses = $record->actions()->orderByRaw('time ASC')->pluck('status')->toArray();

            if ($relatedRecords->isEmpty()) {
                return view('filament.officer.resources.request-resource.pages.actions.emptyactions', [
                    'records' => $record,
                ]);
            }

            return view('filament.officer.resources.request-resource.pages.actions.viewactions', [
                'records' => $relatedRecords,
                'statuses' => $actionStatuses,
            ]);
        });
    }
}
