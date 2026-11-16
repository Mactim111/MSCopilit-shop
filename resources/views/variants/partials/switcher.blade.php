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

        — НОВОЕ: в свитчере участвуют только ДОСТУПНЫЕ варианты (is_active и stock - reserved > 0)
          плюс текущий. Плитка опции, которой нет ни у одного такого варианта, скрывается.
--}}

@if(isset($variantMatrix) && $variantMatrix->isNotEmpty())

    @php
        /**
         * НОВОЕ: пул вариантов для свитчера.
         * Доступные для заказа + текущий (чтобы активная плитка и ссылка не ломались,
         * даже если текущий вариант уже закончился и страницу открыли по прямой ссылке).
         * Фильтрация идёт в памяти по уже загруженным $product->variants: запросов к БД нет.
         */
        $switcherVariants = $product->variants->filter(
            fn($v) => $v->id === $variant->id || $v->isSellable()
        );
    @endphp

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

                // ID опций текущего варианта по ВСЕМ остальным осям — «контекст» варианта.
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

                // ИЗМЕНЕНО В ЛОГИКЕ, ОПИСАННОЙ ВЫШЕ: варианты, совпадающие по всем остальным осям, ищем в пуле
                // доступных ($switcherVariants), а не! среди всех! вариантов товара.
                $sameAxisVariants = $switcherVariants->filter(function ($v) use ($otherIds) {
                    return $otherIds->every(
                        fn($id) => $v->propertyOptions->contains('id', $id)
                    );
                });

                // НОВОЕ: плитки, которые показываем. Опция выводится, только если она есть
                // хотя бы у одного варианта из пула. Если доступных вариантов с опцией
                // «Белый» не осталось, плитки «Белый» не будет ни у каких вариантов.
                $visibleItems = collect($axis['options'])->filter(
                    fn($item) => $switcherVariants->contains(
                        fn($v) => $v->propertyOptions->contains('id', $item['option']->id)
                    )
                );
            @endphp

            {{-- НОВОЕ: если у ОСИ не! осталось! ни! одной! ПЛИТКИ, заголовок сназванием СВОЙСТВА тоже не! выводим! --}}
            @continue($visibleItems->isEmpty())

            {{-- Блок свойства --}}
            <div class="w-full mb-[32px]">

                {{-- Заголовок оси (свойства) --}}
                <div class="pb-[12px] text-[15px] text-[#232F20] text-left font-bold">
                    {{ $property->title }}
                </div>

                {{-- Плитки опций --}}
                <div class="flex flex-wrap gap-[12px]">

                    {{-- ИЗМЕНЕНО: перебираем $visibleItems вместо $axis['options'] --}}
                    @foreach ($visibleItems as $item)
                        @php
                            /** @var \App\Models\PropertyOption $option */
                            $option = $item['option'];

                            // Опция активного варианта.
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
                            $isOwnOption = $sameAxisVariants->contains(
                                fn($v) => $v->propertyOptions->contains('id', $option->id)
                            );

                            /**
                             * НЕСТРОГИЙ выбор варианта:
                             * ищем ЛЮБОЙ вариант, содержащий выбранную опцию.
                             * сортируем по количеству совпадений по другим осям,
                             * чтобы переход был максимально предсказуемым.
                             */

                            // ИЗМЕНЕНО в логике ВЫШЕ: целевой вариант ищем только! в пуле доступных.
                            // Сортируем по числу совпадений по остальным осям.
                            // Ссылка никогда не ведёт на закончившийся вариант
                            // (кроме текущего, если он сам закончился).
                            $targetVariant = $switcherVariants
                                ->filter(fn($v) => $v->propertyOptions->contains('id', $option->id))
                                ->sortByDesc(fn($v) => $otherIds
                                    ->filter(fn($id) => $v->propertyOptions->contains('id', $id))
                                    ->count())
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

                        {{-- РОДНАЯ плитка --}}
                        @elseif($isOwnOption)
                            <a href="{{ $url }}"
                               class="h-[34px] flex items-center justify-center
                                      text-[15px] text-[#232F20] px-[12px]
                                      bg-[#F4F4F4] rounded-[5px]">
                                {{ $option->value }}
                            </a>

                        {{-- ЧУЖАЯ плитка (блеклая) --}}
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