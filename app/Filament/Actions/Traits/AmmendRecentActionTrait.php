<?php

namespace App\Filament\Actions\Traits;

use App\Enums\RequestStatus;
use App\Models\Request;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait AmmendRecentActionTrait
{
    protected array $statuses = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'amend';

        $this->visible(function (Request $record) {
            return $record->action?->status->major() &&
                $record->action?->status !== RequestStatus::PUBLISHED &&
                $record->action?->user->is(Auth::user());
        });
        $this->hidden(fn (Request $record) => $record->action->status == RequestStatus::RESOLVED);

        $this->color('primary');

        $this->icon('gmdi-draw-o');

        $this->modalAlignment(Alignment::Left);

        $this->modalIcon('gmdi-draw-o');

        $this->modalDescription(function (Request $record) {
            $html = <<<HTML
                You can ammend your recent <b>“<i>{$record->action->status->getLabel('nounForm', false)}</i>”</b> remarks and attachments here.
            HTML;

            return str($html)->toHtmlString();
        });

        $this->modalWidth('3xl');

        $this->successNotificationTitle('Ammended successfully');

        $this->fillForm(fn (Request $record) => [
            'status' => $record->action->status,
            'remarks' => $record->action->remarks,
            'paths' => $record->action->attachment?->paths->toArray() ?? [],
            'files' => $record->action->attachment?->files->toArray() ?? [],
        ]);

        $this->form([
            Select::make('status')
                ->hidden(fn () => empty($this->statuses))
                ->columnSpanFull()
                ->options(fn () => $this->statuses)
                ->required(),
            RichEditor::make('remarks')
                ->columnSpanFull()
                ->label('Remarks')
                ->placeholder('Please provide a reason for retracting this request...')
                ->required(),
            FileUpload::make('paths')
                ->label('Attachments')
                ->placeholder(fn (string $operation) => match ($operation) {
                    'view' => 'Click the icon at the left side of the filename to download',
                    default => null,
                })
                ->directory(fn (Request $record) => "attachments/tmp/{$record->action->id}")
                ->preserveFilenames()
                ->storeFileNamesIn('files')
                ->multiple()
                ->maxFiles(5)
                ->downloadable()
                ->previewable(false)
                ->maxSize(1024 * 4)
                ->removeUploadedFileButtonPosition('right')
                ->rule(fn (Request $record, Get $get) => function ($attribute, $value, $fail) use ($record, $get) {
                    $files = collect($get('paths'))->map(fn (TemporaryUploadedFile|string $file) => [
                        'file' => $file instanceof TemporaryUploadedFile
                            ? $file->getClientOriginalName()
                            : $record->action->attachment->files[$file],
                        'hash' => $file instanceof TemporaryUploadedFile
                            ? hash_file('sha512', $file->getRealPath())
                            : hash_file('sha512', storage_path("app/public/$file")),
                    ]);

                    if (($duplicates = $files->duplicates('hash'))->isNotEmpty()) {
                        $dupes = $files->filter(fn ($file) => $duplicates->contains($file['hash']))->unique();

                        $fail('Please do not upload the same files ('.$dupes->map->file->join(', ').') multiple times.');
                    }
                }),
        ]);

        $this->action(function (Request $record, self $action, array $data) {
            $ammendment = $record->action;

            $ammendment->update(['remarks' => $data['remarks']]);

            $new = collect($data['files'])->filter(fn (string $file, string $path) => str($path)->startsWith('attachments/tmp'));

            if ($new->isNotEmpty() || count($data['files']) != $ammendment->attachment?->files->count()) {
                $files = $new->mapWithKeys(fn (string $file) => [
                    str(str()->ulid())
                        ->prepend("attachments/action-{$ammendment->id}-")
                        ->append(($ext = pathinfo($file, PATHINFO_EXTENSION)) ? ".$ext" : '')
                        ->lower()
                        ->toString() => "attachments/tmp/{$ammendment->id}/$file",
                ]);

                $files->each(fn (string $file, string $path) => Storage::move("public/$file", "public/$path"));

                $files = collect($data['files'])->mapWithKeys(fn ($file, $path) => [$files->search(fn ($tmp) => $tmp === $path) ?: $path => $file]);

                Process::run(['rm', '-rf', Storage::path('public/attachments/tmp/'.$ammendment->id)]);

                $attachment = $ammendment->attachment;

                if ($attachment !== null) {
                    $attachment->files = $files->map(fn ($file) => basename($file))->toArray();

                    $attachment->paths = $files->keys()->toArray();
                } else {
                    $attachment = $ammendment->attachment()->create([
                        'files' => $files->map(fn ($file) => basename($file))->toArray(),
                        'paths' => $files->keys()->toArray(),
                    ]);
                }

                $attachment->sanitize();

                $ammendment->touch();
            }

            Notification::make()
                ->title('Request Ammenbded')
                ->icon(RequestStatus::AMMENDED->getIcon())
                ->iconColor(RequestStatus::AMMENDED->getColor())
                ->body($record->category->name.' ( '.$record->subcategory->name.' ) '.'</br>'.auth()->user()->name.' : '.'</br>'.$data['remarks'])
                ->sendToDatabase($record->assignees);
            $action->sendSuccessNotification();

        });
    }

    public function statuses(?array $statuses, ?string $type = null): static
    {
        $statuses = collect($statuses)->mapWithKeys(fn (RequestStatus|string $status) => $status instanceof RequestStatus
                ? [$status->value => $status->getLabel($type)]
                : [mb_strtolower($status) => RequestStatus::from(mb_strtolower($status))->getLabel($type)]
        );

        $this->statuses = $statuses->toArray();

        return $this;
    }
}
