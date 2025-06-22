<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Store extends Model
{
    use HasFactory, HasUuids;
    
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'store_category_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function storeCategory()
    {
        return $this->belongsTo(StoreCategory::class);
    }
}
