<div class="flex gap-2">
    @foreach (config('rentify.locales') as $loc)
        @if (app()->getLocale() === $loc)
            <span class="font-bold underline" aria-current="true">
                {{ strtoupper($loc) }}
            </span>
        @else
            {{-- Form to change language. route('locale.switch', $loc) creates /idioma/pt or /idioma/en,
             using the name in routes/web.php --}}
            <form method="POST" action="{{ route('locale.switch', $loc) }}" class="inline">
                {{-- Hidden with token CSRF. Without it, Laravel gives error 419.
                Blocks other websites to submit this form in another persons name --}}
                @csrf

                <button type="submit" class="cursor-pointer opacity-60 hover:opacity-100">
                    {{ strtoupper($loc) }}
                </button>
            </form>
        @endif
    @endforeach
</div>
