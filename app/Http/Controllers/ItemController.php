<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Category;
use App\Models\Item;
use App\Http\Requests\UpdateItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Throwable;


class ItemController extends Controller
{
    /**
     * Display a listing of the resources
     */
    public function index()
    {
        //with categories loads the categories of all items in one extra query, instead of one query per item
        //latest -> newest first
        $items = Item::with('categories')->latest()->paginate(10);
        //makes $items available in view items
        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // the form needs the list of categories so the user can choose
        // name is an accessor (current locale), not a column, so sort in memory
        return view('items.create', [
            'categories' => Category::all()->sortBy(
                fn ($category) => Str::ascii($category->name),
                SORT_NATURAL | SORT_FLAG_CASE
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        //StoreItemRequest has already validates the data
        $imagePath = $request->file('image')?->store('items', 'public');
        //if anything fails, the item is not saved
        DB::transaction(function () use ($request, $imagePath) {
            //creates the item already linked to the logged in user (it comes from SESSION)
            //safe except categories => only the validated fields minus categories
            //because categories is not a column of the items table
            $item = $request->user()->items()->create($request->safe()->except(['categories' ,'image']));

            //sync => writes category ids into the pivot table category_item
            $item->categories()->sync($request->validated('categories'));
            if ($imagePath) {
                $image = new Image();
                $image->path = $imagePath;
                $item->images()->save($image);
            }
        });


        //redirect after a write
        //this way, refreshing the page does not duplicate a item
        return redirect()->route('items.index')->with('success', __('items.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //load fetches the categories and the owner of item
        $item->load('categories', 'owner', 'deliverLocation','images');

        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        //ItemPolicy, only the owner can edit
        Gate::authorize('update', $item);

        return view('items.edit', [
            'item' => $item->load('categories'),
            'categories' => Category::all()->sortBy(
                fn ($category) => Str::ascii($category->name),
                SORT_NATURAL | SORT_FLAG_CASE
            ),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        //authorization already ran in UpdateItemRequest::authorize(), which asks ItemPolicy::update
        $imagePath = $request->file('image')?->store('items', 'public');

        try {
            DB::transaction(function () use ($request, $item, $imagePath) {
                //only the validated item fields are written
                $item->update($request->safe()->except(['categories', 'image']));
                $item->categories()->sync($request->validated('categories'));

                if ($imagePath) {
                    $image = new Image();
                    $image->path = $imagePath;
                    $item->images()->save($image);
                }
            });
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('items.show', $item)->with('success', __('items.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        //ItemPolicy, only the owner can delete
        Gate::authorize('delete', $item);

        //any reservation blocks delete. item_reservation.item_id has no cascade
        if ($item->reservations()->exists()) {
            return redirect()->route('items.show', $item)->with('error', __('items.has_reservations'));
        }

        //read the paths first: the image rows disappear together with the item (cascadeOnDelete)
        $paths = $item->images()->pluck('path')->all();

        $item->delete();

        //files only after the row is gone, so a failed delete never leaves an item with broken images
        Storage::disk('public')->delete($paths);

        return redirect()->route('items.index')->with('success', __('items.deleted'));
    }
}
