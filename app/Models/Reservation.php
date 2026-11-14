<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    // List of fields that the user can fill using create()/fill()/update().
    protected $fillable = ['start_date', 'end_date'];

    //reservation belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //reservation has many items
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_reservation') // N..N relationship using the pivot table
                    ->withPivot('quantity', 'price_per_day') // Include extra columns stored in the pivot table
                    ->withTimestamps(); // Automatically manage created_at and updated_at timestamps on the pivot table
    }

    //reservation has one payment
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_price' => 'decimal:2',
            'status' => ReservationStatus::class
        ];
    }

    //reservation has 0..2 reviews to the users
    public function userReviews()
    {
        return $this->hasMany(UserReview::class);
    }

    //reservation has 0..N reviews to the items
    public function itemReviews()
    {
        return $this->hasMany(ItemReview::class);
    }
}
