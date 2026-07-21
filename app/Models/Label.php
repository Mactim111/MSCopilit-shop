<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    use HasFactory;
    use SoftDeletes;    

    protected $fillable = ['title', 'slug', 'logo', 'position'];

    public function product_variant_labels()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_labels', 'label_id', 'product_variant_id')->withTimestamps();
    }
}
