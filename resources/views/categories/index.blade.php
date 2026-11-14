@extends('layout')

@section('title', __('categories.index_title'))

@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('items.index_title') }}</h1>
    <a href="{{ route('categories.index') }}">
        {{ __('categories.index_title') }}
    </a>
    <a href="{{ route('categories.create') }}" class="inline-block mb-4 rounded border px-3 py-1">
        {{ __('categories.create_button') }}
    </a>

    @forelse ($categories as $cat)
        <article class="mb-3 rounded border p-3">
            <h2 class="font-semibold">
                {{ $cat->name_en }}
            </h2>
            <h2 class="font-semibold">
                {{ $cat->name_pt }}
            </h2>
        </article>
    @empty
        <p>{{ __('categories.empty') }}</p>
    @endforelse
@endsection
