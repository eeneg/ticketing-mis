<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body>
<div class="">
    <div class="font-bold text-xl mb-5">Request History</div>
          @php
        $totalAssigned = count(array_filter($statuses, function($status) {
            return $status === 'Assigned';
        }));
    @endphp

    @foreach ($records as $record)
    <div class="flex flex-row">
        <div class="text-xl font-semibold items-center px-5 flex flex-col">

            @if($record->status == 'Responded')
                <x-heroicon-m-check-circle class="w-9 h-9 text-emerald-500 bg-white rounded-full border-2 border-emerald-500" />
            @elseif($record->status == 'Assigned' && $totalAssigned == 1)
                <x-heroicon-s-arrow-left-circle class="w-9 h-9 text-amber-500 bg-white rounded-full border-2 border-amber-500" />
            @elseif($record->status == 'Assigned')
                <x-heroicon-m-arrow-path class="w-9 h-9 text-white bg-blue-500 rounded-full border-2 border-white p-1" />
            @else
                <x-heroicon-c-x-circle class="w-9 h-9 text-purple-500 bg-white rounded-full border-2 border-purple-500 mt-1" />
            @endif

            <div class="flex flex-row my-2">
                <div class="border-r border-slate-700">&nbsp;</div>
                <div class="border-l border-slate-700">&nbsp;</div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="flex flex-row">
                <div
                    @class(['text-xl',
                    'ml-2',
                    'text-amber-500' => $record->status == 'Assigned',
                    'text-violet-500' => $record->status == 'Rejected',
                    'text-emerald-500' => $record->status == 'Responded',
                    ])
                >
                    @if ($record->status == 'Assigned')
                        @php
                            $totalAssigned--;
                        @endphp
                        @if ($totalAssigned == 0)
                            {{ $record->status }}

                        @else
                        Reassigned
                            @php
                                $iconChange = true;
                            @endphp
                        @endif
                    @else
                        {{ $record->status }}
                    @endif
                    &nbsp;

                </div>
                <div class="text-xl">
                    @if($record->status == 'Responded')
                    <p>and awating for user Response</p>

                    @elseif($record->status == 'Assigned')
                    <p>to to Support Team</p>

                    @endif
                </div>
            </div>
            <div class="flex flex-row items-center px-5">
                <div class="text-base font-semibold opacity-60">
                    {{$record->user->name}}
                </div>
                <div class="text-sm  opacity-35">
                    &nbsp;on&nbsp;{{Carbon\Carbon::parse($record->time)->format('j\t\h \o\f
                    F Y \a\t h:m:s A')}}
                </div>
            </div>
        </div>

      </div>
    @endforeach
    {{-- DEFAULT CREATED AT --}}
    <div class="flex flex-row">
        <div class="text-xl font-semibold items-center px-5 flex flex-col">
                <x-heroicon-m-check-circle class="w-9 h-9 text-emerald-500 bg-white rounded-full border-2 border-emerald-500" />
        </div>

        <div class="flex flex-col ">

            <div class="flex flex-row">
                <div class="text-xl ml-2 var(--bs-info)">
                    Request Created
                    &nbsp;
                </div>
            </div>
            <div class="flex flex-row items-center px-5">
                <div class="text-base font-semibold opacity-60">
                    {{$record->user->name}}
                </div>
                <div class="text-sm opacity-35">
                &nbsp;on&nbsp;{{ Carbon\Carbon::parse($record->created_at)->format('j\t\h \o\f F Y \a\t h:i:s A') }}
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
