@use('App\Models\Item')

@extends('layout')

@section('title', __('items.create_title'))

@section('content')
<div class="flex flex-col justify-center items-center">
    <h1 class="text-2xl font-bold mb-4">{{ __('items.create_title') }}</h1>

    <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
        @csrf
        @include('items._form', ['item' => new Item])

        <button type="submit" class="rounded border p-2 my-4">{{ __('items.save') }}</button>
    </form>
</div>
@endsection
