<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliverLocationRequest;
use App\Models\DeliverLocation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeliverLocationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(DeliverLocationRequest  $request)
    {
        DeliverLocation::query()->create($request->validated());
        return back()->with('success', __('deliver_location.created'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DeliverLocationRequest $request, DeliverLocation $deliverLocation)
    {
        //fill only flags attributes whose value differs from DB
        $deliverLocation->fill($request->validated());

        if ($deliverLocation->isClean())
            return back()->with('warning', __('deliver_location.unchanged'));

        $deliverLocation->save();
        return back()->with('success', __('deliver_location.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliverLocation $deliverLocation)
    {
        // destroy has no FormRequest, so authorization is checked here
        Gate::authorize('manage', DeliverLocation::class);

        // block deleting a location that users still have selected
        if ($deliverLocation->users()->exists())
            return back()->withErrors(['deliver_location' => __('deliver_location.beingUsed')]);

        try {
            $deliverLocation->delete();
        } catch (QueryException) {
            return back()->withErrors(['deliver_location' => __('deliver_location.beingUsed')]);
        }
        return back()->with('success', __('deliver_location.deleted'));
    }
}
