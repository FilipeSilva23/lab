<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ChooseDeliverLocationRequest;
use App\Http\Requests\UpdateUserDeliverLocationRequest;
use App\Models\DeliverLocation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role === UserRole::Student) {
            return view('dashboard.student', [
                'locations' => DeliverLocation::all()->sortBy(
                    fn ($location) => Str::ascii($location->name), SORT_NATURAL | SORT_FLAG_CASE
                ),
            ]);
        } elseif ($user->role === UserRole::Service) {
            return view('dashboard.service');
        } elseif ($user->role === UserRole::Admin) {
            return view('dashboard.admin', [
                'deliverLocations' => DeliverLocation::all()->sortBy(
                    fn ($location) => Str::ascii($location->name), SORT_NATURAL | SORT_FLAG_CASE
                ),
                'users' => User::query()
                    ->with('deliverLocation')
                    ->whereIn('role', [UserRole::Student->value, UserRole::Service->value])
                    ->orderBy('name')
                    ->paginate(10),
            ]);
        }

        //fallback
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        abort(403, __('auth.invalid_role'));
    }

    public function updateDeliverLocation(ChooseDeliverLocationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->deliver_location_id = $request->validated('deliver_location_id');
        $user->save();

        return back()->with('success', __('deliver_location.updated'));
    }

    public function updateUserDeliverLocation(UpdateUserDeliverLocationRequest $request, User $user): RedirectResponse
    {
        $user->deliver_location_id = $request->validated('deliver_location_id');
        // isClean is true when the chosen location is the one the user already has
        if ($user->isClean())
            return back()->with('warning', __('deliver_location.user_unchanged', ['name' => $user->name]));
        $user->save();

        return back()->with('success', __('deliver_location.user_updated', ['name' => $user->name]));
    }
}
