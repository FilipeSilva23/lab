@extends('layout')
@section('content')
<div class="flex flex-col gap-8">
    <div class="p-2">
        <h1 class="text-3xl font-bold">{{__('dashboard.student')}}</h1>

        <div>
            <p><strong>{{__('dashboard.name')}}:</strong> {{ auth()->user()->name }}</p>
            <p><strong>{{__('dashboard.email')}}:</strong> {{ auth()->user()->email }}</p>
        </div>
    </div>

    <div class="flex flex-col justify-center items-center self-center p-6 mx-6 bg-green-700 rounded-xl text-white">
        <h2 class="text-xl font-bold mb-6">
            {{ __('deliver_location.label') }}: {{ auth()->user()->deliverLocation?->name ?? __('deliver_location.none') }}
        </h2>

        <form method="POST" action="{{ route('dashboard.deliver-location.update') }}"
              class="flex flex-col gap-8">
            @csrf
            {{-- HTML forms only support GET and POST, so this hidden field makes Laravel treat the request as PATCH --}}
            @method('PATCH')
            <h3 class="text-l font-semibold">{{__('dashboard.change_location')}}</h3>
            <select name="deliver_location_id" required class="p-2 text-center bg-green-600 rounded-lg">
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected($location->id == auth()->user()->deliver_location_id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="p-4 bg-green-600 rounded-lg font-bold cursor-pointer">
                {{ __('deliver_location.save') }}
            </button>
        </form>
    </div>
</div>
@endsection
