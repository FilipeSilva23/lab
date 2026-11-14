@if (session('success'))
    <div role="status" class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-red-800">
        {{ session('error') }}
    </div>
@endif
