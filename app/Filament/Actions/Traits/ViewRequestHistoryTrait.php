<?php

namespace App\Filament\Actions\Traits;

use App\Models\Request;

trait ViewRequestHistoryTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'history';

        $this->modalSubmitAction(false);

        $this->color('primary');

        $this->icon('heroicon-o-presentation-chart-line');

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
