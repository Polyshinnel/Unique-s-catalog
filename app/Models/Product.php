<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productState()
    {
        return $this->belongsTo(ProductState::class, 'product_state_id');
    }

    public function productAvailable()
    {
        return $this->belongsTo(ProductAvailable::class, 'product_availability_id');
    }

    public function productLocation()
    {
        return $this->belongsTo(ProductLocation::class, 'product_location_id');
    }

    public function productPrice()
    {
        return $this->hasOne(ProductPrice::class)->where('show', true);
    }

    public function productPriceAll()
    {
        return $this->hasOne(ProductPrice::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImages::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImages::class)->where('main_image', true);
    }

    public function productManager()
    {
        return $this->hasOne(ProductManager::class);
    }

    public function productCharacteristics()
    {
        return $this->hasOne(ProductCharacteristics::class);
    }

    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id');
    }
}
