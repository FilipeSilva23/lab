@extends('layout')

@section('title', __('categories.edit_title'))

@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('categories.edit_title') }}</h1>

    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name_pt" class="block mb-1">
                {{ __('categories.name_pt') }}
            </label>

            <input
                id="name_pt"
                name="name_pt"
                type="text"
                value="{{ old('name_pt', $category->name_pt) }}"
                maxlength="255"
                required
                class="w-full rounded border p-2"
            >

            @error('name_pt')
            <p class="mt-1 text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="name_en" class="block mb-1">
                {{ __('categories.name_en') }}
            </label>

            <input
                id="name_en"
                name="name_en"
                type="text"
                value="{{ old('name_en', $category->name_en) }}"
                maxlength="255"
                required
                class="w-full rounded border p-2"
            >

            @error('name_en')
            <p class="mt-1 text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="rounded border px-4 py-2">
            {{ __('categories.save') }}
        </button>

        <a href="{{ route('categories.index') }}" class="ml-2">
            {{ __('categories.cancel') }}
        </a>
    </form>

@endsection
