<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model
{   
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
        'category_id',
    ];
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}