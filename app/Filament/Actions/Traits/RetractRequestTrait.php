<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait RetractRequestTrait
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'retract';

        $this->color(RequestStatus::RETRACTED->getColor());

        $this->visible(fn (Request $record) => $record->action?->status === RequestStatus::PUBLISHED);

        $this->icon('heroicon-c-newspaper');

        $this->modalAlignment(Alignment::Left);

        $this->modalIcon('heroicon-c-newspaper');

        $this->modalDescription('Are you sure you want to retract this request?');

        $this->modalWidth('3xl');

        $this->successNotificationTitle('Request retracted successfully');

        $this->form([
            RichEditor::make('remarks')
                ->columnSpanFull()
                ->label('Remarks')
                ->placeholder('Please provide a reason for retracting this request...')
                ->required(),
            Repeater::make('attachments')
                ->columnSpanFull()
                ->label('Attachments')
                ->columnSpanFull()
                ->deletable(false)
                ->addable(false)
                ->reorderable(false)
                ->hint('Help')
                ->hintIcon('heroicon-o-question-mark-circle')
                ->hintIconTooltip('Please upload a maximum file count of 5 items and file size of 4096 kilobytes.')
                ->simple(
                    FileUpload::make('paths')
                        ->placeholder(fn (string $operation) => match ($operation) {
                            'view' => 'Click the icon at the left side of the filename to download',
                            default => null,
                        })
                        ->directory(fn (Request $record) => "attachments/tmp/{$record->id}")
                        ->preserveFilenames()
                        ->multiple()
                        ->maxFiles(5)
                        ->downloadable()
                        ->previewable(false)
                        ->maxSize(1024 * 4)
                        ->removeUploadedFileButtonPosition('right')
                )
                ->rule(fn () => function ($attribute, $value, $fail) {
                    $files = collect(current($value)['paths'])->map(fn (TemporaryUploadedFile|string $file) => [
                        'file' => $file instanceof TemporaryUploadedFile
                            ? $file->getClientOriginalName()
                            : current($value)['files'][$file],
                        'hash' => $file instanceof TemporaryUploadedFile
                            ? hash_file('sha512', $file->getRealPath())
                            : hash_file('sha512', storage_path("app/public/$file")),
                    ]);

                    if (($duplicates = $files->duplicates('hash'))->isNotEmpty()) {
                        $dupes = $files->filter(fn ($file) => $duplicates->contains($file['hash']))->unique();

                        $fail('Please do not upload the same files ('.$dupes->map->file->join(', ').') multiple times.');
                    }
                }
                ),
        ]);

        $this->action(function (Request $record, self $action, array $data) {
            $retraction = $record->actions()->create([
                'request_id' => $record->id,
                'user_id' => Auth::id(),
                'status' => RequestStatus::RETRACTED,
                'time' => now(),
                'remarks' => $data['remarks'],
            ]);

            if (($attachments = collect(current($data['attachments'])))->isNotEmpty()) {
                $files = $attachments
                    ->mapWithKeys(fn (string $file) => [
                        str(str()->ulid())
                            ->prepend("attachments/action-{$retraction->id}-")
                            ->append(($ext = pathinfo($file, PATHINFO_EXTENSION)) ? ".$ext" : '')
                            ->lower()
                            ->toString() => $file,
                    ])
                    ->each(fn (string $file, string $path) => Storage::move("public/$file", "public/$path"));

                $retraction->attachment()->create([
                    'files' => $files->map(fn ($file) => basename($file))->toArray(),
                    'paths' => $files->keys()->toArray(),
                ]);

                Process::run(['rm', '-rf', Storage::path('public/attachments/tmp/'.$record->id)]);
            }

            $action->sendSuccessNotification();
        });
    }
}
