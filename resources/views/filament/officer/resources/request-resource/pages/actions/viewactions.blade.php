<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> --}}
      {{-- <link rel="stylesheet" href="app.css" /> --}}
    @vite('resources/css/app.css')
    <div class="font-bold text-xl">Request History</div>
</head>
<body>
<div class="space-y-">
    @foreach ($records as $record)
      <div class="flex flex-row ">
{{-- border-2 border-red-800 --}}
        <div class="text-xl font-semibold  items-center px-5 flex flex-col">
           @if($record->status == 'Responded')
         <x-heroicon-m-check-circle  class="w-9 h-9 text-emerald-500 bg-white rounded-full border-2 border-emerald-500" />
        @elseif($record->status == 'Assigned')
         <x-heroicon-s-arrow-left-circle class="w-9 h-9 text-amber-500 bg-slate-900 rounded-full border-2 border-amber-500" />
        @else
         <x-heroicon-c-x-circle class="w-9 h-9 text-purple-500 bg-white rounded-full border-2 border-purple-500 mt-1" />
        @endif
                <div class="flex flex-row my-2">
                    <div class="border-r border-slate-600">&nbsp;</div>
                    <div class="border-l border-slate-600">&nbsp;</div>
                </div>
        </div>

        <div class="flex flex-col ">

            <div class="flex flex-row">
                <div class="text-xl ml-2
                    {{ $record->status == 'Rejected' ? 'text-violet-500 font-semibold' : '' }}
                    {{ $record->status == 'Responded' ? 'text-emerald-500 font-semibold' : '' }}
                    {{ $record->status == 'Assigned' ? 'text-amber-500 font-semibold' : '' }}
                     ">
                    {{$record->status}}&nbsp;
            </div>
            <div class="text-xl ">
                @if($record->status == 'Responded')
                <p>and awating for user Response</p>

                @elseif($record->status == 'Assigned')
                <p>to to Support Team</p>

                @endif</div>
            </div>
            <div class="flex flex-row items-center px-5">

            <div class="text-base font-semibold opacity-60">{{$record->user->name}}</div>
            <div class="text-sm  opacity-35">&nbsp;on&nbsp;{{Carbon\Carbon::parse($record->time)->format('j\t\h \o\f
              F Y \a\t h:m:s A')}}</div>
            </div>
        </div>
      </div>
    @endforeach
</div>

</body>
</html>
