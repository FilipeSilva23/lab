@extends('layout')

@section('title', __('items.edit_title'))

@section('content')
<div class="flex flex-col justify-center items-center">
    <h1 class="text-2xl font-bold mb-4">{{ __('items.edit_title') }}</h1>

    <form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('items._form')

        <button type="submit" class="rounded border p-2 my-4">{{ __('items.save') }}</button>
    </form>
</div>
@endsection
