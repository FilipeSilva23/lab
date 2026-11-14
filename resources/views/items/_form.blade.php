@php
    // after a failed validation use what was submitted (even if empty), otherwise the saved categories
    $selected = session()->hasOldInput()
        ? old('categories', [])
        : ($item->exists ? $item->categories->pluck('id')->all() : []);

    //same class for every input
    $inputClass = 'w-full max-w-md rounded border border-gray-300 bg-gray-100 px-2 py-1';
@endphp

<div class="flex flex-col gap-4">
    <div>
        <label for="title">{{ __('items.title') }}</label>
        <br>
        <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" maxlength="255" required class="{{$inputClass}}">
    </div>

    <div>
        <label for="description">{{ __('items.description') }}</label>
        <br>
        <textarea id="description" name="description" maxlength="512" rows="4" class="{{$inputClass}}">
            {{ old('description', $item->description) }}
        </textarea>
    </div>

    <div>
        <label for="quantity">{{ __('items.quantity') }}</label>
        <br>
        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $item->quantity ?? 1) }}" min="1" step="1" required class="{{$inputClass}}">
    </div>

    <div>
        <label for="price_per_day">{{ __('items.price_per_day') }} (€)</label>
        <br>
        <input type="number" id="price_per_day" name="price_per_day" value="{{ old('price_per_day', $item->price_per_day) }}" min="0" max="9999.99" step="0.01" required class="{{$inputClass}}">
    </div>

    <div>
        <label for="deposit_amount">{{ __('items.deposit_amount') }} (€)</label>
        <br>
        <input type="number" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', $item->deposit_amount) }}" min="0" max="9999.99" step="0.01" class="{{$inputClass}}">
        <p class="text-sm">{{ __('items.deposit_hint') }}</p>
    </div>
    <div>
        <label for="image" class="block mb-1">
            {{ __('items.image') }}
        </label>

        <input
            type="file"
            id="image"
            name="image"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            onchange="document.getElementById('image-name').textContent =
            this.files.length ? this.files[0].name : @js(__('items.no_file_selected'))"
        >

        <label for="image" class="inline-block cursor-pointer rounded border px-3 py-2">
            {{ __('items.choose_file') }}
        </label>

        <span id="image-name" class="ml-2">
        {{ __('items.no_file_selected') }}
    </span>

        @error('image')
        <p class="mt-1 text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <fieldset>

        <legend>{{ __('items.categories') }}</legend>

        @foreach ($categories as $category)
            <label class="block">
                {{-- categories[] sends an array of ids; the server checks that each one exists --}}
                <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selected))>
                {{ $category->name }}
            </label>

        @endforeach
        <a href="{{ route('categories.create', ['return_to' => 'items.create']) }}"
                       class="inline-block my-2 rounded border px-3 py-1">
            {{ __('categories.add_from_item') }}
        </a>
    </fieldset>

</div>
