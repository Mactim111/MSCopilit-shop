<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $fillable = [
        'user_id',
        'label',
        'address_line',
        'city',
        'street',
        'house',
        'apartment',
        'entrance',
        'floor',
        'state',
        'zip',
        'country',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        $parts = collect([$this->city]);
        $hasStructuredStreet = filled($this->street) || filled($this->house);

        if ($hasStructuredStreet) {
            $parts->push(filled($this->street) ? 'ул. ' . $this->street : null);
            $parts->push(filled($this->house) ? 'д. ' . $this->house : null);
            $parts->push(filled($this->apartment) ? 'кв. ' . $this->apartment : null);
        } else {
            $parts->push($this->address_line);
        }

        $parts->push(filled($this->entrance) ? 'подъезд ' . $this->entrance : null);
        $parts->push(filled($this->floor) ? 'этаж ' . $this->floor : null);

        return $parts
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }

    public function formattedAddressLine(): string
    {
        return collect([
            filled($this->street) ? 'ул. ' . $this->street : null,
            filled($this->house) ? 'д. ' . $this->house : null,
            filled($this->apartment) ? 'кв. ' . $this->apartment : null,
            filled($this->entrance) ? 'подъезд ' . $this->entrance : null,
            filled($this->floor) ? 'этаж ' . $this->floor : null,
        ])
            ->filter()
            ->implode(', ');
    }

}
