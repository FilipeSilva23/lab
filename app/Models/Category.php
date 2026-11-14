<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name_pt', 'name_en'];

    public function items() :belongsToMany{
        return $this ->belongsToMany(Item::class, 'category_item');
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => app()->getLocale() === 'en' ? $this->name_en : $this->name_pt);
    }
}
