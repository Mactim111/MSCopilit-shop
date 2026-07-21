<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogFilterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'f'         => ['sometimes', 'array'],
            'f.*'       => ['sometimes'],
            'f.*.*'     => ['string', 'max:100'],
            // СТРОКИ НИЖЕ решили не включать в rules(), так как Laravel не поддерживает wildcard * в середине ключа типа 'f_*_min' — он работает только 
            // для вложенных массивов через точку ('f.*.value'). То есть правило 'f_*_min' просто тихо игнорируется и никакой валидации не происходит.
            // Эти поля будут проверяться вручную в withValidator().
            // 'f_*_min'   => ['sometimes', 'numeric', 'min:0'],
            // 'f_*_max'   => ['sometimes', 'numeric', 'min:0'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric', 'min:0'],
            // 'brand' => ['sometimes', 'array'],
            // 'brand.*' => ['integer', 'exists:brands,id'],
            'brand' => ['sometimes', 'string'], // CSV-строка
            'sort'      => ['sometimes', 'string', 'in:newest,price_asc,price_desc'],
            'page'      => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function validFilters(): array
    {
        $filters = $this->validated();
        if (isset($filters['f']) && is_array($filters['f'])) {
            $filters['f'] = array_filter(
                array_map(fn ($v) => is_array($v) ? array_filter($v) : $v, $filters['f'])
            );
        }

        if ($this->has('brand')) {
            $filters['brand'] = explode(',', $this->get('brand'));
        }

        // ВНИМАНИЕ! $this->validated() возвращает только поля из rules(). Поскольку f_ram_min, f_ves_max и т.д. там не объявлены — они в validated() не попадут. 
        // Поэтому в validFilters() их нужно добрать вручную из $this->all() отдельным циклом, что и делает код выше.

        // Добавляем range-параметры вручную, так как они не проходят через rules()
        foreach ($this->all() as $key => $value) {
            if (preg_match('/^f_[a-z0-9_]+_(min|max)$/', $key) && is_numeric($value)) {
                $filters[$key] = (float) $value;
            }
        }

        return $filters;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->all() as $key => $value) {
                // Матчим f_anything_min и f_anything_max
                if (!preg_match('/^f_[a-z0-9_]+_(min|max)$/', $key)) {
                    continue;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                if (!is_numeric($value) || (float) $value < 0) {
                    $validator->errors()->add(
                        $key,
                        "Параметр «{$key}» должен быть числом не меньше 0."
                    );
                }
            }
        });
    }
}
