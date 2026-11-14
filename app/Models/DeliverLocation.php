<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverLocation extends Model
{
    protected $fillable = ['name_pt', 'name_en'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_pt;
    }
}
