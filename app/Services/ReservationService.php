<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function create(User $user, array $items_and_quantity, Carbon $start_date, Carbon $end_date): Reservation
    {
        //carbon is mutable so we need to copy it first
        $start = $start_date->copy()->startOfDay();
        $end = $end_date->copy()->startOfDay();
        $days = $this->validateDates($start, $end);

        $this->validateItems($items_and_quantity);

        //we use transaction so if any error occurs, everything is discarded and we prevent "half" changes
        return DB::transaction(function () use ($user, $items_and_quantity, $start, $end, $days) {
            $items = $this->lockItems(array_keys($items_and_quantity));
            $this->checkStock($items, $items_and_quantity, $start, $end);

            $total_price = 0;
            foreach ($items as $item)
                //quantity*price_per_day*days
                $total_price+=$items_and_quantity[$item->id]*$item->price_per_day*$days;

            $reservation = new Reservation(['start_date'=>$start, 'end_date'=>$end]);
            $reservation->user_id = $user->id;
            $reservation->status = ReservationStatus::Confirmed;
            $reservation->total_price=round($total_price,2);
            $reservation->save();

            //the price is copied now, so later price changes don't affect this reservation
            foreach($items as $item) {
                $reservation->items()->attach($item->id, [
                    'quantity' => $items_and_quantity[$item->id],
                    'price_per_day' => $item->price_per_day,
                ]);
            }

            return $reservation;
        });
    }

    private function validateDates(Carbon $start, Carbon $end): int
    {
        if($start->isAfter($end) || Carbon::today()->isAfter($start))
            throw ValidationException::withMessages(['dates' => __('reservation.invalid_dates')]);

        $maxDays = (int) config('rentify.max_days');
        $days = (int) round($start->diffInDays($end)) + 1;
        if($days>$maxDays)
            throw ValidationException::withMessages(['dates' => __('reservation.too_long', ['max' => $maxDays])]);

        return $days;
    }

    private function validateItems(array $items_and_quantity): void
    {
        if($items_and_quantity === [])
            throw ValidationException::withMessages(['items' => __('reservation.no_items')]);

        $quantity = array_values($items_and_quantity);
        foreach ($quantity as $x)
            if(!is_int($x) || $x <1)
                throw ValidationException::withMessages(['items' => __('reservation.invalid_quantity')]);
    }

    private function lockItems(array $ids)
    {
        //lock the rows until the end of the transaction to avoid deadlocks
        //this has to be the first read inside the transaction
        $items = Item::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();

        if($items->count() !== count($ids))
            throw ValidationException::withMessages(['items' => __('reservation.item_not_found')]);

        //pluck retrieves all the values for a given key, here we count the different owners
        if($items->pluck('user_id')->unique()->count() !== 1)
            throw ValidationException::withMessages(['items' => __('reservation.different_owners')]);

        return $items;
    }

    private function checkStock(Collection $items, array $items_and_quantity, Carbon $start, Carbon $end)
    {
        //a reservation conflicts with ours if it starts before our end + gap and ends after our start - gap
        $gap = (int) config('rentify.gap_days');
        $start_gap = $start->copy()->subDays($gap);
        $end_gap = $end->copy()->addDays($gap);

        $conflicting_ids = DB::table('reservations')
            ->where('status', ReservationStatus::Confirmed->value)
            ->whereDate('start_date', '<=', $end_gap)
            ->whereDate('end_date', '>=', $start_gap)
            ->pluck('id');

        foreach($items as $item) {
            //quantity of certain item inside those reservations
            $already_reserved = (int) DB::table('item_reservation')
                ->where('item_id', $item->id)
                ->whereIn('reservation_id', $conflicting_ids)
                ->sum('quantity');

            //quantity the user wants of this item
            $requested = $items_and_quantity[$item->id];

            //already reserved + requested can't be more than the stock
            if($already_reserved + $requested > $item->quantity) {
                throw ValidationException::withMessages(['items' => __('reservation.not_enough_stock', ['item' => $item->title])]);
            }
        }
    }
}
