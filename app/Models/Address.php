<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $fillable = [
        'user_id', 'label', 'address_line', 'city', 'state', 'zip', 'country'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        return collect([
            $this->country,
            $this->city,
            $this->state,
            $this->zip,
            $this->address_line,
        ])
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }

}
