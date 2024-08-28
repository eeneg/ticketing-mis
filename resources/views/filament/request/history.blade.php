@use('App\Enums\RequestStatus')

<section wire:poll.3s>
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
                        <div class="p-2 mb-3 text-base bg-gray-100 rounded-md dark:bg-gray-800">
                            <span class="text-sm text-neutral-500">
                                <svg class="inline w-6 h-6 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                    <path d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"/>
                                </svg>
                                Remarks
                            </span>

                            <div class="prose max-w-none !border-none text-base text-gray-950 dark:prose-invert focus-visible:outline-none dark:text-white sm:text-sm sm:leading-6">
                                {{ str($action->remarks)->toHtmlString() }}
                            </div>
                        </div>
                    @endif

                    @if ($action->attachment && $action->attachment->paths->isNotEmpty())
                        <div class="p-2 space-y-2 text-base bg-gray-100 rounded-md dark:bg-gray-800">
                            <span class="text-sm text-neutral-500">
                                <svg class="inline w-6 h-6 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                    <path d="M330-240q-104 0-177-73T80-490q0-104 73-177t177-73h370q75 0 127.5 52.5T880-560q0 75-52.5 127.5T700-380H350q-46 0-78-32t-32-78q0-46 32-78t78-32h370v80H350q-13 0-21.5 8.5T320-490q0 13 8.5 21.5T350-460h350q42-1 71-29.5t29-70.5q0-42-29-71t-71-29H330q-71-1-120.5 49T160-490q0 70 49.5 119T330-320h390v80H330Z"/>
                                </svg>
                                Attachments
                            </span>

                            <ul class="max-w-md space-y-1 text-sm text-gray-500 list-inside dark:text-gray-400">
                                @foreach ($action->attachment?->files as $file => $name)
                                    <li class="flex items-center">
                                        <a href="{{ url('storage/' . $file) }}" download="{{ $name }}">
                                            <svg class="inline w-6 h-6 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                                <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                                            </svg>
                                            {{ $name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
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

                    on {{ $request->created_at->format('jS \of F Y \a\t H:i:s') }} on ({{ $request->created_at->diffForHumans() }}).
                </time>
            </li>
        </ol>
    </div>
</section>
