@extends('layout')
@section('content')
<div class="flex flex-col gap-8">
    <div class="p-2">
        <h1 class="text-3xl font-bold">{{__('dashboard.admin')}}</h1>

        <div>
            <p><strong>{{__('dashboard.name')}}:</strong> {{ auth()->user()->name }}</p>
            <p><strong>{{__('dashboard.email')}}:</strong> {{ auth()->user()->email }}</p>
        </div>
    </div>

    {{-- Feedback of every form on this page (locations and users), shown at the top so the admin always sees it --}}
    <div class="flex flex-col items-center mx-6">
        @if (session('success'))
            <div role="status" class="mb-4 p-3 rounded-lg bg-green-700 text-white font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div role="alert" class="mb-4 p-3 rounded-lg bg-yellow-300 font-bold">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <ul class="mb-4 p-3 rounded-lg bg-red-500 text-white font-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Div of Deliver Locations --}}
    <div class="flex flex-col justify-center items-center p-6 mx-6 bg-green-200 rounded-xl">
        <h2 class="text-3xl font-bold mb-6">{{__('deliver_location.title')}}</h2>
        <div class="flex flex-row w-full p-10 gap-10">
            {{-- Create New Location --}}
            <div class="w-1/2 flex justify-center items-start">
                <form method="POST" action="{{ route('deliver-locations.store') }}"
                      class="flex flex-col justify-center items-center gap-4 p-6 rounded-lg bg-green-700 text-white">
                    @csrf
                    <h3 class="text-xl font-bold">{{__('deliver_location.create')}}</h3>
                    <div class="flex flex-row justify-center items-center gap-4">
                        <input id="name_pt" type="text" name="name_pt" placeholder="{{__('deliver_location.name_pt')}}"
                               required autofocus value="{{ old('name_pt') }}" class="p-2 text-center bg-green-600 rounded-lg">
                        <input id="name_en" type="text" name="name_en" placeholder="{{__('deliver_location.name_en')}}"
                               required autofocus value="{{ old('name_en') }}" class="p-2 text-center bg-green-600 rounded-lg">
                    </div>

                    <button type="submit" class="p-4 bg-green-600 rounded-lg font-bold cursor-pointer">
                        {{__('deliver_location.submit')}}
                    </button>
                </form>
            </div>

            {{-- List of Deliver Locations --}}
            <div class="w-1/2 flex flex-col items-center gap-4">
                <h3 class="text-2xl font-bold">{{__('deliver_location.list_title')}}</h3>
                @foreach ($deliverLocations as $location)
                    <div class="flex flex-col justify-center items-center gap-4 p-6 rounded-lg bg-green-700 text-white">
                        <h3 class="text-xl font-bold">{{$location->name_pt}} / {{$location->name_en}}</h3>
                        <form method="POST" action="{{ route('deliver-locations.update', $location) }}"
                              class="flex flex-col gap-4">
                            @csrf
                            {{-- HTML forms only send GET/POST; this spoofs PATCH so the request hits the "update" route --}}
                            @method('PATCH')
                            <div class="flex flex-row justify-center items-center gap-8">
                                <input type="text" name="name_pt" value="{{ $location->name_pt }}" required placeholder="PT"
                                       class="text-center bg-green-500 rounded-lg p-2">
                                <input type="text" name="name_en" value="{{ $location->name_en }}" required placeholder="EN"
                                       class="text-center bg-green-500 rounded-lg p-2">
                            </div>
                            <button type="submit" class="p-4 bg-green-600 rounded-lg font-bold cursor-pointer">
                                {{__('deliver_location.edit')}}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('deliver-locations.destroy', $location) }}">
                            @csrf
                            {{-- Same spoofing, but for DELETE so the request hits the "destroy" route --}}
                            @method('DELETE') {{-- DELETE to delete --}}
                            <button type="submit" class="p-4 bg-red-400 rounded-lg font-bold cursor-pointer">
                                {{__('deliver_location.delete')}}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Div of Deliver Locations of each user --}}
    <div class="flex flex-col justify-center items-center p-6 mx-6 bg-green-200 rounded-xl">
        <h2 class="text-3xl font-bold mb-6">{{__('deliver_location.users_title')}}</h2>

        <table class="w-full text-center text-white bg-green-700 rounded-lg">
            <thead class="border-4 border-red-800">
            <tr>
                <th class="p-3">{{__('dashboard.name')}}</th>
                <th class="p-3">{{__('dashboard.email')}}</th>
                <th class="p-3">{{__('dashboard.role')}}</th>
                <th class="p-3">{{__('deliver_location.label')}}</th>
            </tr>
            </thead>
            <tbody class="divide-y-4 divide-blue-800">
                @foreach ($users as $user)
                    <tr>
                        <td class="p-3">{{$user->name}}</td>
                        <td class="p-3">{{$user->email}}</td>
                        <td class="p-3">{{__('dashboard.roles.'.$user->role->value)}}</td>
                        <td class="p-3">
                            <form method="POST" action="{{route('users.deliver-location.update', $user)}}">
                                @csrf
                                {{-- Same PATCH spoofing as the other forms --}}
                                @method('PATCH')
                                <select name="deliver_location_id" required
                                        aria-label="{{ __('deliver_location.label') }} - {{ $user->name }}"
                                        class="p-2 bg-green-600 rounded-lg">
                                    {{-- placeholder is only selected while the user has no location; disabled so it can't be submitted --}}
                                    <option value="" disabled @selected(is_null($user->deliver_location_id))>
                                        {{__('deliver_location.none')}}
                                    </option>
                                    @foreach ($deliverLocations as $location)
                                        <option value="{{ $location->id }}" @selected($location->id == $user->deliver_location_id)>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="p-2 bg-green-600 rounded-lg font-bold cursor-pointer">
                                    {{__('deliver_location.save')}}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $users->links() }}
        </div>

    </div>
</div>
@endsection
