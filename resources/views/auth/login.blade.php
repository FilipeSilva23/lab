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
    <a href="{{ url('/') }}"
       class="absolute top-4 left-4 bg-green-900 hover:bg-green-700 p-4 rounded-lg text-sm font-medium transition-colors">
        {{__('login.back')}}
    </a>

    <div class="absolute top-4 right-4 bg-green-900 hover:bg-green-700 p-4 rounded-lg cursor-pointer">
        @include('partials.language')
    </div>

    <div class=" bg-green-900 p-6 rounded-lg text-center flex flex-col">
        <h2 class="text-2xl font-bold mb-6">{{__('login.login')}}</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-10 ml-10 mr-10 text-lg">
            @csrf

            {{-- Email --}}
            <div class="flex flex-col">
                <label for="email" class="p-2">
                    {{__('login.email')}}
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="p-2"
                       required autofocus placeholder="exemple@ufp.edu.pt">
            </div>

            {{-- Password --}}
            <div class="flex flex-col">
                <label for="password" class="p-2">
                    {{__('login.password')}}
                </label>
                <input id="password" type="password" name="password" class="p-2" required placeholder="******************">
            </div>

            {{-- Remember Me --}}
            <div class="flex flex-col p-2 gap-2">
                <label for="remember">
                    {{__('login.remember')}}
                </label>
                <input type="checkbox" id="remember" name="remember" class="scale-150 accent-amber-600">
            </div>

            {{-- Submit --}}
            <div>
                <button class="w-full bg-amber-600 hover:bg-amber-500 text-white font-bold p-4
                        rounded-lg cursor-pointer transition-colors shadow-md"
                        type="submit">
                    {{__('login.submit')}}
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
