@if($errors->any())
    <div role="alert" class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
