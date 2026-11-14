<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UserReview extends Model
{
    protected $fillable = ['classification', 'description'];

    //A review belongs to 1 reservation
    public function reservation():BelongsTo{
        return $this->belongsTo(Reservation::class);
    }

    //A review has one author
    public function author():BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    //A review has one person being reviewed
    public function reviewee():BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}
