<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('manage', Category::class);
        return view('categories.index', ['categories' => Category::all()->sortBy(
            fn ($category) => Str::ascii($category->name), SORT_NATURAL | SORT_FLAG_CASE
        )]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('manage', Category::class);
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        if ($request->input('return_to') === 'items.create') {
        return redirect()
            ->route('items.create')
            ->with('success', __('categories.created'));
    }
        Category::query()->create($request->validated());
        return redirect()->route('categories.index')->with('success', __('categories.created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        Gate::authorize('manage', Category::class);
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return redirect()->route('categories.index')->with('success', __('categories.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        Gate::authorize('manage', Category::class);

        // cascadeOnDelete removes only the category_item rows, the items stay
        $category->delete();

        return redirect()->route('categories.index')->with('success', __('categories.deleted'));
    }
}
