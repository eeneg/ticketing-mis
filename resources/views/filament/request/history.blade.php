@use('App\Enums\RequestStatus')

<section wire:poll>
    <div class="pl-[0.75rem] space-y-3">
        <ol class="relative border-gray-200 border-s dark:border-gray-700">
            @foreach ($request->actions as $action)
                <li class="mb-4 ms-6">
                    <span
                        class='absolute flex items-center justify-center w-6 h-6 rounded-full bg-custom-500 -start-3 ring-8 ring-white dark:ring-gray-900'
                        @style(["--c-500:var(--{$action->status->getColor()}-500)"])
                    >
                        <x-filament::icon class="w-5 h-5 text-neutral-100" icon="{{ $action->status->getIcon() }}"/>
                    </span>

                    <h3 class="flex items-center mb-1 text-base">
                        <span>
                            <span class='text-custom-500' @style(["--c-500:var(--{$action->status->getColor()}-500)"])>
                                @if (in_array($action->status, [RequestStatus::PUBLISHED, RequestStatus::SCHEDULED, RequestStatus::ASSIGNED]))
                                    {{
                                        $request->actions->filter(fn ($act) => $act->status === $action->status)->reverse()->first()->is($action)
                                            ? $action->status->getLabel()
                                            : "Re{$action->status->value}"
                                    }}
                                @else
                                    {{ $action->status->getLabel() }}
                                @endif
                            </span>

                            @if ($action->remarks && $action->status->minor() && $action->status !== RequestStatus::ASSIGNED)
                                <span class="text-xs align-text-bottom">
                                    {{ $action->remarks }}
                                </span>
                            @endif
                        </span>
                    </h3>

                    <time class="block mb-2 text-sm font-light leading-none text-neutral-500">
                        <span class="font-bold">
                            {{ $action->user->name }}
                        </span>

                        on {{ $action->created_at->format('jS \of F Y \a\t H:i:s') }} ({{ $action->created_at->diffForHumans() }}).
                    </time>

                    @if ($action->remarks && ($action->status->major() || $action->status === RequestStatus::ASSIGNED))
                        <blockquote class="p-3 text-base bg-gray-100 rounded-md dark:bg-gray-800">
                            <svg class="w-5 h-5 mb-2 text-gray-400 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 14">
                                <path d="M6 0H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3H2a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3h-1a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Z"/>
                            </svg>

                            <p class="text-base leading-none">
                                {{
                                    str($action->remarks)
                                        ->replace('<ul>', '<ul class="list-disc list-inside">')
                                        ->replace('<ol>', '<ol class="list-decimal list-inside">')
                                        ->toHtmlString()
                                }}
                            </p>
                        </blockquote>
                    @endif
                </li>
            @endforeach

            <li class="mb-4 ms-6">
                <span
                    class='absolute flex items-center justify-center w-6 h-6 rounded-full bg-custom-500 -start-3 ring-8 ring-white dark:ring-gray-900'
                    @style(["--c-500:var(--info-500)"])
                >
                    <x-filament::icon class="w-5 h-5 text-neutral-100" icon="gmdi-timer-o"/>
                </span>

                <h3 class="flex items-center mb-1 text-base">
                    <span @class(['text-custom-500']) @style(["--c-500:var(--info-500)"])>
                        Created
                    </span>
                </h3>

                <time class="block mb-2 text-sm font-light leading-none text-neutral-500">
                    <span class="font-bold">
                        {{ $request->requestor?->name }}
                    </span>

                    on {{ $request->created_at->format('jS \of F Y \a\t H:i:s.') }}
                </time>
            </li>
        </ol>
    </div>
</section>
