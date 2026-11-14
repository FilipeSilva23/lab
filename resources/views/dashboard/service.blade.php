@extends('layout')
@section('content')
<div class="flex flex-col gap-8">
    <div class="p-2">
        <h1 class="text-3xl font-bold">{{__('dashboard.service')}}</h1>

        <div>
            <p><strong>{{__('dashboard.name')}}:</strong> {{ auth()->user()->name }}</p>
            <p><strong>{{__('dashboard.email')}}:</strong> {{ auth()->user()->email }}</p>
            <p><strong>{{ __('deliver_location.label') }}:</strong>
                {{ auth()->user()->deliverLocation?->name ?? __('deliver_location.none') }}
            </p>
        </div>
    </div>



</div>
@endsection
