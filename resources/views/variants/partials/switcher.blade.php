{{--
    Partial: свитчер вариантов товара.

    Подключение в шаблоне карточки варианта:
        @include('variants.partials.switcher')

    Переменные (передаются из ProductVariantController::show()):
        $variant       — текущий активный вариант (ProductVariant)
        $product       — товар-родитель (Product, с загруженными relations)
        $variantMatrix — Collection из $product->variant_matrix

    Маршрут перехода между вариантами: catalog.variant (один параметр {variant})
        route('catalog.variant', $targetVariant)

    Логика свитчера:
        — Для каждого свойства (ось вариативности) выводим плитки с опциями.
        — При клике на плитку ищем вариант, у которого:
            а) есть выбранная опция по текущей оси
            б) сохранены все уже выбранные опции по остальным осям
          Это «умный» переход: меняем только одну ось, остальные сохраняем.
        — Недоступные опции (нет варианта в наличии) — зачёркнуты, некликабельны.
        — Если заполнен color_hex — показываем цветной кружок рядом с текстом.
--}}

@if(isset($variantMatrix) && $variantMatrix->isNotEmpty())

    <div class="flex flex-col gap-[16px]">

        @foreach ($variantMatrix as $axis)
            @php
                /** @var \App\Models\Property $property */
                $property = $axis['property'];

                // Текущее значение этого свойства у активного варианта.
                $currentOption = $variant->propertyOptions
                    ->firstWhere('property_id', $property->id);
            @endphp

            <div>
                {{-- Заголовок оси: «Цвет корпуса: Чёрный» --}}
                <p class="text-[14px] font-bold text-[#231F20] mb-[8px]">
                    {{ $property->title }}:
                    <span class="font-normal text-gray-500">
                        {{ $currentOption?->value }}
                    </span>
                </p>

                {{-- Плитки опций --}}
                <div class="flex flex-wrap gap-[8px]">
                    @foreach ($axis['options'] as $item)
                        @php
                            /** @var \App\Models\PropertyOption $option */
                            $option    = $item['option'];
                            $available = $item['available'];
                            $isActive  = $variant->propertyOptions->contains('id', $option->id);

                            /**
                             * «Умный» поиск целевого варианта:
                             * ищем вариант у которого:
                             *   — есть опция $option (по текущей оси)
                             *   — есть все опции текущего варианта по ОСТАЛЬНЫМ осям
                             *
                             * Пример: у варианта «Чёрный / 8ГБ / 128ГБ» меняем цвет на Синий →
                             * ищем вариант «Синий / 8ГБ / 128ГБ», а не просто любой синий.
                             */
                            $targetVariant = $product->variants->first(
                                function ($v) use ($option, $variant, $property) {
                                    // Должна быть выбранная опция по текущей оси.
                                    if (!$v->propertyOptions->contains('id', $option->id)) {
                                        return false;
                                    }
                                    // Все опции по остальным осям должны совпадать.
                                    $otherIds = $variant->propertyOptions
                                        ->where('property_id', '!=', $property->id)
                                        ->pluck('id');

                                    // из-за того, что после первой смены ЦВЕТА пропадает возможность ЕГО ПОВТОРНОЙ! смены, пробовали убрать проверку ВЫШЕ на совпадение 
                                    // остальных опций, ТО ЕСТЬ отменить ФИКСАЦИЮ по остальным! ОСЯМ! ТИПА при смене цвета → ищем любой! вариант с этим цветом!
                                    // А! при смене памяти → ищем любой вариант с этой памятью - НО! потом после запуска npm run dev, npm run build ВСЁ! ЗАРАБОТАЛО!

                                    return $otherIds->every(
                                        fn($id) => $v->propertyOptions->contains('id', $id)
                                    );
                                }
                            );

                            // URL перехода на найденный вариант.
                            // Маршрут catalog.variant принимает один параметр {variant}.
                            $url = ($targetVariant && $available)
                                ? route('catalog.variant', $targetVariant)
                                : null;
                        @endphp

                        @if ($url)
                            {{-- Доступная опция — кликабельная ссылка --}}
                            <a href="{{ $url }}"
                               title="{{ $option->value }}"
                               class="inline-flex items-center gap-[6px]
                                      px-[12px] py-[6px] rounded-lg border text-[14px] font-medium
                                      transition-all duration-150
                                      {{ $isActive
                                          ? 'border-[#DC092E] bg-[#FFF4F4] text-[#DC092E]
                                             ring-2 ring-[#DC092E] ring-offset-1'
                                          : 'border-gray-300 text-[#231F20]
                                             hover:border-[#DC092E] hover:text-[#DC092E]'
                                      }}"
                            >
                                @if ($option->color_hex)
                                    <span class="w-[14px] h-[14px] rounded-full border border-gray-200 flex-none"
                                          style="background-color: {{ $option->color_hex }}"></span>
                                @endif
                                {{ $option->value }}
                            </a>

                        @else
                            {{-- Недоступная опция — зачёркнута, некликабельна --}}
                            <span title="{{ $option->value }} — нет в наличии"
                                  class="relative inline-flex items-center gap-[6px]
                                         px-[12px] py-[6px] rounded-lg border border-gray-200
                                         text-[14px] font-medium text-gray-300
                                         cursor-not-allowed overflow-hidden">
                                @if ($option->color_hex)
                                    <span class="w-[14px] h-[14px] rounded-full border border-gray-200
                                                 opacity-40 flex-none"
                                          style="background-color: {{ $option->color_hex }}"></span>
                                @endif
                                {{ $option->value }}
                                {{-- Диагональная зачёркивающая линия --}}
                                <span class="absolute inset-0 flex items-center justify-center
                                             pointer-events-none">
                                    <span class="w-full h-px bg-gray-300 rotate-[-20deg] block"></span>
                                </span>
                            </span>
                        @endif

                    @endforeach
                </div>
            </div>

        @endforeach

    </div>

@endif