@extends('layout')

@section('title', $item->title)

@section('content')
<div class="flex flex-col justify-center items-center text-center">
    <h1 class="text-2xl font-bold mb-4">{{ $item->title }}</h1>
    @foreach ($item->images as $image)
        <img
            src="{{ asset('storage/' . $image->path) }}"
            alt="{{ $item->title }}"
            class="mx-auto mb-4 max-w-sm rounded"
        >
    @endforeach
    <p class="mb-4">{{ $item->description }}</p>

    <div class="mb-6 flex flex-col gap-2">
        <p><span class="font-semibold">{{ __('items.price_per_day') }}:</span>
            @if ((float) $item->price_per_day === 0.0)
                {{ __('items.free') }}
            @else
                {{ $item->price_per_day }} €
            @endif
        </p>

        @if ($item->deposit_amount !== null)
            <p><span class="font-semibold">{{ __('items.deposit_amount') }}:</span>
                {{ $item->deposit_amount }} €
            </p>
        @endif

        <p><span class="font-semibold">{{ __('items.quantity') }}:</span>
            {{ $item->quantity }}
        </p>

        <p><span class="font-semibold">{{ __('items.categories') }}:</span>
            {{ $item->categories->pluck('name')->join(', ') }}
        </p>

        <p><span class="font-semibold">{{ __('items.owner') }}:</span>
            {{ $item->owner->name }}
        </p>

        <p><span class="font-semibold">{{ __('items.deliver_location') }}:</span>
            {{ $item->deliverLocation?->name ?? '—' }}
        </p>
    </div>

    <div class="flex items-center gap-4">
        <a href="{{ route('items.index') }}" class="rounded border p-2">{{ __('items.back') }}</a>

        {{-- @can asks ItemPolicy; It only hides the buttons: the real check is on the server --}}
        @can('update', $item)
            <a href="{{ route('items.edit', $item) }}" class="rounded border p-2">{{ __('items.edit') }}</a>
        @endcan

        @can('delete', $item)
            {{-- browsers only send GET and POST, so DELETE is sent as POST plus @method('DELETE') --}}
            <form method="POST" action="{{ route('items.destroy', $item) }}" class="inline"
                  onsubmit="return confirm(@js(__('items.confirm_delete')))">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border p-2 cursor-pointer bg-red-400">{{ __('items.delete') }}</button>
            </form>
        @endcan
    </div>
</div>
@endsection
