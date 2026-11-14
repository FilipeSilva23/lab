<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Item extends Model
{

    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'quantity',
        'price_per_day',
        'deposit_amount',
    ];
    protected function casts(): array{
        return [
            'price_per_day' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo{
        return $this->belongsTo(User::class, 'user_id');
    }
    public function categories(): BelongsToMany{
        return $this -> belongsToMany(Category::class, 'category_item');
    }

    public function images():HasMany{
        return $this-> hasMany(Image::class, 'item_id');
    }

    public function reservations():BelongsToMany{
        return $this -> belongsToMany(Reservation::class, 'item_reservation')
            ->withPivot('quantity', 'price_per_day')->withTimestamps();
    }

    public function itemReviews(): HasMany{
        return $this->hasMany(ItemReview::class, 'item_id');
    }

    // The pickup spot is always the owner's current one; nothing is stored on the item
    public function deliverLocation(): HasOneThrough{
        return $this->hasOneThrough(
            DeliverLocation::class,
            User::class,
            'id', // users.id
            'id', // deliver_locations.id
            'user_id', // items.user_id
            'deliver_location_id' // users.deliver_location_id
        );
    }

}
