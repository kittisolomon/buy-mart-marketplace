<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasFactory, HasUuids;

    public $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'store_id',
        'name',
        'description',
        'price',
        'quantity',
        'product_image_url', 
        'product_image_id',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
