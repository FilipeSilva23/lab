<header class="flex justify-between items-center p-2 font-bold bg-green-900 text-white">
    <nav class="flex items-center gap-8">
        <a href="{{route('dashboard')}}"
           @class(['underline underline-offset-4' => request()->routeIs('dashboard')])
           aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
            {{ __('menu.dashboard') }}
        </a>

        <a href="{{ route('items.index') }}"
           @class(['underline underline-offset-4' => request()->routeIs('items.*')])
           aria-current="{{ request()->routeIs('items.*') ? 'page' : 'false' }}">
            {{ __('menu.items') }}
        </a>

        {{-- @auth and @endauth is used so the logout button only appears if user is logged in --}}
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="cursor-pointer">
                    {{ __('login.logout') }}
                </button>
            </form>
        @endauth
    </nav>
    <div class="rounded-full bg-green-700 px-3 py-1">
        @include('partials.language')
    </div>
</header>
