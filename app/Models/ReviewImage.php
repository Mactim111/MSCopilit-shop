<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'review_id',
        'path',
        'media_type',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
