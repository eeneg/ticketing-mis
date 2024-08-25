<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
</head>
<body>
    <div class="font-bold text-xl mb-5">Request History</div>
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
                    Alex
                </div>
                <div class="text-sm opacity-35">
                &nbsp;on&nbsp;{{ Carbon\Carbon::parse($records->created_at)->format('j\t\h \o\f F Y \a\t h:i:s A') }}
                {{-- August 18, 204 at 16:34 PM --}}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
