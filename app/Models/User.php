<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class
        ];
    }

    //returns every item the user owns
    public function items() {
        return $this->hasMany(Item::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function writtenReviews(): HasMany
    {
        return $this->hasMany(UserReview::class, 'author_id');
    }
    public function receivedReviews(): HasMany
    {
        return $this->hasMany(UserReview::class, 'reviewee_id');
    }

    public function deliverLocation(): BelongsTo
    {
        return $this->belongsTo(DeliverLocation::class);
    }
}
