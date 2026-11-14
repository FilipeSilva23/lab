<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemReview extends Model
{
    protected $fillable = ['classification', 'description'];

    //A review belongs to 1 reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    //A review has one item it belongs
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
