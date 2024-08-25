<?php

namespace App\Filament\User\Resources\RequestResource\Pages;

use App\Filament\User\Resources\RequestResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateRequest extends CreateRecord
{
    protected static string $resource = RequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requestor_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $attachment = $this->record->attachment()->first();

        if ($attachment?->paths->isNotEmpty()) {
            $paths = $attachment->paths->map(fn ($path) => str($path)->prepend('public/'));

            $paths->each(fn ($path) => Storage::move($path, 'public/attachments/request-' . $this->record->id . '-'. $path->basename()));

            $attachment->paths = $paths->map(fn ($path) => 'attachments/request-' . $this->record->id . '-' . $path->basename());

            $attachment->files = $attachment->files->mapWithKeys(function ($file, $path) {
                return ['attachments/request-' . $this->record->id . '-' . str($path)->basename() => $file];
            });

            $attachment->sanitize();
        }
    }
}
