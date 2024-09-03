<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Models\Request;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait UpdateRequestTraits
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'update';

        $this->color('info');

        $this->visible(fn ($record) => $record->action->status === RequestStatus::STARTED);

        $this->button();

        $this->disabled(function ($record) {
            return $record->currentUserAssignee->response->name == 'REJECTED';
        });

        $this->form([
            Select::make('status')
                ->required()
                ->options([
                    RequestStatus::COMPLETED->value => RequestStatus::COMPLETED->getLabel(),
                    RequestStatus::SUSPENDED->value => RequestStatus::SUSPENDED->getLabel(),
                ])
                ->reactive()
                ->native(false),
            RichEditor::make('remarks')
                ->required(fn (Get $get): bool => $get('status') === RequestStatus::SUSPENDED->value),
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

        $this->action(function (array $data, $record) {
            $update = $record->action()->create([
                'user_id' => Auth::id(),
                'actions.request_id' => $record->id,
                'status' => $data['status'],
                'time' => now(),
                'remarks' => $data['remarks'],

            ]);
            $record->currentUserAssignee()->update([
                'response' => UserAssignmentResponse::COMPLETED,
                'responded_at' => now(),
            ]);
            if (($attachments = collect(current($data['attachments'])))->isNotEmpty()) {
                $files = $attachments
                    ->mapWithKeys(fn (string $file) => [
                        str(str()->ulid())
                            ->prepend("attachments/action-{$update->id}-")
                            ->append(($ext = pathinfo($file, PATHINFO_EXTENSION)) ? ".$ext" : '')
                            ->lower()
                            ->toString() => $file,
                    ])
                    ->each(fn (string $file, string $path) => Storage::move("public/$file", "public/$path"));

                $update->attachment()->create([
                    'files' => $files->map(fn ($file) => basename($file))->toArray(),
                    'paths' => $files->keys()->toArray(),
                ]);

                Process::run(['rm', '-rf', Storage::path('public/attachments/tmp/'.$record->id)]);
            }

            Notification::make()
                ->title('Submitted Successfully!')
                ->success()
                ->send();

            Notification::make()
                ->title('Request '.$data['status'])
                ->body(str("Request “<i>{$record->subject}</i>” has been {$data['status']} by ".auth()->user()->name.'.')->toHtmlString())
                ->icon(RequestStatus::tryFrom($data['status'])?->getIcon())
                ->iconColor(RequestStatus::tryFrom($data['status'])?->getColor())
                ->sendToDatabase($record->requestor);

            $this->successNotificationTitle('Request updated');
        });
    }
}
