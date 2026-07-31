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
        — НЕСТРОГИЙ режим (как у 5 Элемент):
            * ВСЕ плитки кликабельны
            * переход ВСЕГДА происходит на существующий вариант
            * НИКОГДА нельзя попасть на несуществующую комбинацию
        — Визуал:
            * активная опция → белый фон + чёрная рамка
            * «родная» опция → серый фон + чёрный текст
            * «чужая» опция → серый фон + светло‑серый текст
        — Родные/чужие опции определяются НЕ по всему товару,
          а только среди вариантов, совпадающих по ВСЕМ остальным осям.
--}}

@if(isset($variantMatrix) && $variantMatrix->isNotEmpty())

    <div class="flex flex-col">

        @foreach ($variantMatrix as $axis)
            @php
                /** @var \App\Models\Property $property */
                $property = $axis['property'];

                // Текущее значение этого свойства у активного варианта.
                $currentOption = $variant->propertyOptions
                    ->firstWhere('property_id', $property->id);

                /**
                 * Собираем ID всех опций текущего варианта,
                 * кроме текущей оси (свойства).
                 *
                 * Эти опции определяют "контекст" варианта.
                 * Например, для оси "Цвет корпуса" контекстом будут:
                 *   — RAM
                 *   — Storage
                 *   — Region
                 *   — Model
                 *
                 * Именно по этому контексту 5 Элемент определяет,
                 * какие опции являются "родными", а какие "чужими".
                 */
                $otherIds = $variant->propertyOptions
                    ->where('property_id', '!=', $property->id)
                    ->pluck('id');

                /**
                 * Варианты, совпадающие по ВСЕМ остальным осям.
                 *
                 * Это ключевая логика 5 Элемент:
                 *   — родные опции = те, что встречаются среди этих вариантов
                 *   — чужие опции = те, что встречаются только в других комбинациях
                 */
                $sameAxisVariants = $product->variants->filter(function ($v) use ($otherIds) {
                    return $otherIds->every(
                        fn($id) => $v->propertyOptions->contains('id', $id)
                    );
                });
            @endphp

            {{-- Блок свойства --}}
            <div class="w-full mb-[32px]">

                {{-- Заголовок оси (свойства) --}}
                <div class="pb-[12px] text-[15px] text-[#232F20] text-left font-bold">
                    {{ $property->title }}
                </div>

                {{-- Плитки опций --}}
                <div class="flex flex-wrap gap-[12px]">

                    @foreach ($axis['options'] as $item)
                        @php
                            /** @var \App\Models\PropertyOption $option */
                            $option    = $item['option'];

                            /**
                             * Опция активного варианта
                             */
                            $isCurrentVariantOption =
                                $variant->propertyOptions->contains('id', $option->id);

                            /**
                             * Опция "родная":
                             * встречается среди вариантов, совпадающих по остальным осям.
                             *
                             * Пример:
                             *   — Цвет "Зелёный" есть только в вариантах 8/256
                             *   — Значит для варианта 6/128 он "чужой"
                             */
                            $isOwnOption = $sameAxisVariants->contains(function ($v) use ($option) {
                                return $v->propertyOptions->contains('id', $option->id);
                            });

                            /**
                             * НЕСТРОГИЙ выбор варианта:
                             * ищем ЛЮБОЙ вариант, содержащий выбранную опцию.
                             * сортируем по количеству совпадений по другим осям,
                             * чтобы переход был максимально предсказуемым.
                             */
                            $targetVariant = $product->variants
                                ->filter(fn($v) => $v->propertyOptions->contains('id', $option->id))
                                ->sortByDesc(function ($v) use ($variant, $property) {
                                    return $variant->propertyOptions
                                        ->where('property_id', '!=', $property->id)
                                        ->pluck('id')
                                        ->filter(fn($id) => $v->propertyOptions->contains('id', $id))
                                        ->count();
                                })
                                ->first();

                            $url = $targetVariant
                                ? route('catalog.variant', $targetVariant)
                                : null;
                        @endphp

                        {{-- АКТИВНАЯ плитка --}}
                        @if($isCurrentVariantOption)
                            <a href="{{ $url }}"
                               class="h-[34px] flex items-center justify-center
                                      text-[15px] text-[#232F20]
                                      bg-white border border-black rounded-[5px] px-[12px]">
                                {{ $option->value }}
                            </a>

                        {{-- РОДНАЯ плитка (как у 5 Элемент) --}}
                        @elseif($isOwnOption)
                            <a href="{{ $url }}"
                               class="h-[34px] flex items-center justify-center
                                      text-[15px] text-[#232F20] px-[12px]
                                      bg-[#F4F4F4] rounded-[5px]">
                                {{ $option->value }}
                            </a>

                        {{-- ЧУЖАЯ плитка (блеклая, как у 5 Элемент) --}}
                        @else
                            <a href="{{ $url }}"
                               class="h-[34px] flex items-center justify-center
                                      text-[15px] text-[#8C8C8C] px-[12px]
                                      bg-[#F4F4F4] rounded-[5px]">
                                {{ $option->value }}
                            </a>
                        @endif

                    @endforeach

                </div>
            </div>

        @endforeach

    </div>

@endif
