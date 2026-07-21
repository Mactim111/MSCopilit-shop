{{-- Роутер: определяет тип свойства и подключает нужный компонент.
     Все стили — в sub-компонентах (filter-checkbox, radio, range, toggle). --}}

@props([
    'property',
    'active'    => [],
    'activeMin' => null,
    'activeMax' => null,
])

@switch($property->type)

    @case('checkbox')
        <x-catalog.filter-checkbox
            :property="$property"
            :active="(array) $active"
        />
    @break

    @case('radio')
        <x-catalog.filter-radio
            :property="$property"
            :active="(array) $active"
        />
    @break

    @case('range')
        <x-catalog.filter-range
            :property="$property"
            :min="$property->range_min ?? 0"
            :max="$property->range_max ?? 0"
            :active-min="$activeMin ?? $property->range_min ?? 0"
            :active-max="$activeMax ?? $property->range_max ?? 0"
        />
    @break

    @case('toggle')
        <x-catalog.filter-toggle
            :property="$property"
            :active="(bool) (is_array($active) ? ($active[0] ?? false) : $active)"
        />
    @break

@endswitch
