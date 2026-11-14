@extends('layout')

@section('title', __('categories.create_title'))

@section('content')
    <div class="flex flex-col items-center justify-center">
        <h1 class="mb-4 text-2xl font-bold">
            {{ __('categories.create_title') }}
        </h1>

        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <input
                type="hidden"
                name="return_to"
                value="{{ old('return_to', request('return_to')) }}"
            >
            <div class="mb-4">
                <label for="name_pt" class="block mb-1">
                    {{ __('categories.name_pt') }}
                </label>

                <input
                    id="name_pt"
                    name="name_pt"
                    type="text"
                    value="{{ old('name_pt') }}"
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
                    value="{{ old('name_en') }}"
                    maxlength="255"
                    required
                    class="w-full rounded border p-2"
                >

                @error('name_en')
                <p class="mt-1 text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="my-4 rounded border p-2">
                {{ __('categories.save') }}
            </button>
        </form>
    </div>
@endsection

