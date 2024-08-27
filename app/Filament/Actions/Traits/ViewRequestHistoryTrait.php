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

        $this->icon('gmdi-timeline-o');

        $this->slideOver();

        $this->modalWidth('2xl');

        $this->modalContent(function (Request $record) {
            $record->load([
                'actions' => fn ($q) => $q->orderBy('created_at', 'desc'),
                'actions.attachment',
                'attachment',
            ]);

            return view('filament.request.history', [
                'request' => $record,
            ]);
        });
    }
}
