<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rentify</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="relative flex justify-center items-center min-h-screen text-white p-6">
    <div class="absolute top-4 right-4 bg-green-900 p-2 rounded-lg">
        @include('partials.language')
    </div>
    <div class=" bg-green-900  p-6 rounded-lg text-center flex flex-col gap-5">
        <h1 class="text-2xl font-bold">{{__('landing.title')}}</h1>

        <h2 class="text-xl font-bold">{{__('landing.subtitle')}}</h2>

        <p class="whitespace-pre-line leading-tight">{{__('landing.description')}}</p>

        <a href="{{ route('login') }}" class="underline">
            {{ __('login.login') }}
        </a>
    </div>

</div>
</body>
</html>


