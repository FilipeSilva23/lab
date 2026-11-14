@extends('layout')

@section('title', __('items.index_title'))

@section('content')
<div class="flex flex-col mx-auto max-w-3xl gap-2 p-2">
    <h1 class="text-2xl font-bold">{{ __('items.index_title') }}</h1>

    <a href="{{ route('items.create') }}" class="rounded border p-2 my-2 self-start">
        {{ __('items.create_button') }}
    </a>

    @forelse ($items as $item)
        <article class="mb-3 rounded border p-3">
            <h2 class="font-semibold">
                <a href="{{ route('items.show', $item) }}">{{ $item->title }}</a>
            </h2>
            <p>
                {{ __('items.price_per_day') }}:
                @if ((float) $item->price_per_day === 0.0)
                    {{ __('items.free') }}
                    @if ($item->deposit_amount !== null)
                        | {{ __('items.deposit_amount') }}: {{ $item->deposit_amount }} €
                    @endif
                @else
                    {{ $item->price_per_day }} €
                @endif
            </p>
            <p>{{ $item->categories->pluck('name')->join(', ') }}</p>
        </article>
    @empty
        <p>{{ __('items.empty') }}</p>
    @endforelse

    {{-- Renders the pagination links (Previous/Next and page numbers) for the paginated $items --}}
    {{ $items->links() }}
</div>
@endsection
