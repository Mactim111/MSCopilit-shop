@extends('layouts.main')
@section('title', 'Корзина')

@section('content')
<div class="max-w-[1500px] mx-auto py-6">

    @if($items->isEmpty())
        {{-- ЗАГЛУШКА ПУСТОЙ КОРЗИНЫ --}}
        <div class="flex flex-col">
            <p class="text-[28px] text-[#231f20] mb-5 font-bold">В корзине еще нет товаров</p>
            <a href="{{ route('catalog.index') }}"
               class="inline-block text-[#007eff] hover:text-[#0064cc] transition-all duration-200 text-[15px]">
                <span class="text-[#231f20]">Выберите нужный Вам товар из </span>каталога Интернет-магазина
            </a>
        </div>
    @else
        {{-- РАБОЧАЯ КОРЗИНА --}}
        <div class="flex gap-8">
            
            <!-- ЛЕВАЯ КОЛОНКА (8/12) -->
            <div class="w-8/12">
                <div class="flex items-center mb-5 gap-4">
                    <h1 class="text-[28px] font-bold">Корзина</h1>
                    <span class="text-[20px] text-[#8c8c8c]">{{ $items->sum('quantity') }}</span>
                </div>

                <!--
                    Форма массовых действий отделена от форм карточек.
                    Вложенные HTML-формы недопустимы: из-за них браузер отправлял
                    DELETE-запрос на POST-маршрут batch-actions и возникала ошибка 405.
                -->
                <form id="cart-form" action="{{ route('cart.batch-actions') }}" method="POST">
                    @csrf

                    <!-- Блок "Выбрать все" -->
                    <div class="flex items-center pb-4 justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="select-all" class="cursor-pointer"
                                   @checked($allAvailableItemsSelected)>
                            <label for="select-all" class="ml-[10px] text-[15px] font-medium text-[#231f20] cursor-pointer">Выбрать все</label>
                        </div>
                        <button type="submit" name="action" value="delete"
                                @disabled(!$hasSelectedItems)
                                class="text-[15px] text-[#007eff] hover:text-[#0064cc] transition-all duration-200 disabled:text-gray-400 disabled:cursor-not-allowed 
                                disabled:hover:text-gray-400 cursor-pointer">
                            Удалить выбранное
                        </button>
                    </div>
                    <div class="border-t border-dashed border-gray-300 w-full"></div>
                </form>

                <div id="selection-empty-message" @class(['hidden' => $hasSelectedItems, 'mt-4 rounded-xl border border-[#f4dba6] bg-[#fff7dc] px-4 py-3 text-[#6b4a00] text-[15px] leading-relaxed'])>
                    <div class="flex items-stretch gap-5">
                        <span class="flex w-[16px] shrink-0 self-stretch items-center justify-center [&_svg_circle]:stroke-[#F29D00] [&_svg_path]:stroke-[#F29D00]">
                            @include('products.icons.info-icon')
                        </span>
                        <div>
                            <span class="font-semibold">Не выбрано ни одного товара</span><br><span>Отметьте галочкой товары, которые хотите заказать — сразу появится сумма и кнопка «Оформить заказ».</span>
                        </div>
                    </div>
                </div>

                <div>
                    @foreach($items as $item)
                        {{-- Карточка содержит независимые формы удаления и обновления количества. --}}
                        <x-cart-item :item="$item" :selectedItemIds="$selectedItemIds" />
                    @endforeach
                </div>
            </div>

            <!-- ПРАВАЯ КОЛОНКА -->
            <aside class="w-4/12">
                <div class="sticky top-20 border border-gray-200 rounded-xl px-[24px] py-[16px] bg-white shadow-sm">
                    <div id="selection-summary" @class(['hidden' => !$hasSelectedItems])>
                        <div class="flex justify-between items-center mb-4 pb-4 border-b border-dashed border-gray-200">
                            <span class="text-[15px]">Товары (<span id="selected-items-count">{{ $selectedItemsCount }}</span>)</span>
                            <span id="selected-items-total" class="font-bold text-3xl">{{ number_format($selectedTotal, 2, ',', ' ') }} <i class="nbrb-icon">BYN</i></span>
                        </div>
                        <div class="flex justify-between text-[28px] font-bold mb-6">
                            <span>Итого:</span>
                            <span id="selected-grand-total" class="font-bold text-3xl">{{ number_format($selectedTotal, 2, ',', ' ') }} <i class="nbrb-icon">BYN</i></span>
                        </div>
                    </div>

                    <div id="selection-empty-state" @class(['hidden' => $hasSelectedItems, 'mb-6 text-[18px] font-medium leading-relaxed text-[#231F20]'])>
                        <div class="text-[#231F20]">Сумма заказа появится, когда вы отметите галочкой хотя бы один товар.</div>
                    </div>

                    <!-- Кнопка привязана к форме через атрибут form="cart-form" -->
                    <button type="submit" form="cart-form" name="action" value="checkout" @disabled(!$hasSelectedItems)
                            class="block w-full h-12 text-[20px] text-center bg-[#dc092e] border-[#dc092e] text-white rounded-[10px] hover:bg-[#a80723] 
                            hover:border-[#a80723] transition font-semibold cursor-pointer disabled:cursor-not-allowed disabled:bg-[#bdbbbc] disabled:border-[#bdbbbc]">
                        Оформить заказ
                    </button>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.js-item-checkbox');
    const deleteButton = document.querySelector('#cart-form button[name="action"][value="delete"]');
    const checkoutButton = document.querySelector('button[name="action"][value="checkout"]');
    const selectionEmptyMessage = document.getElementById('selection-empty-message');
    const selectionSummary = document.getElementById('selection-summary');
    const selectionEmptyState = document.getElementById('selection-empty-state');
    const selectedItemsCount = document.getElementById('selected-items-count');
    const selectedItemsTotal = document.getElementById('selected-items-total');
    const selectedGrandTotal = document.getElementById('selected-grand-total');

    const formatMoney = (value) => {
        const formatted = Number(value || 0).toLocaleString('ru-RU', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        return `${formatted} <i class="nbrb-icon">BYN</i>`;
    };

    const updateSelectionState = () => {
        const availableCheckboxes = [...checkboxes].filter(cb => !cb.disabled);
        const selectedCheckboxes = availableCheckboxes.filter(cb => cb.checked);
        const selectedCount = selectedCheckboxes.length;
        const selectedTotalCents = selectedCheckboxes.reduce((sum, checkbox) => {
            const item = checkbox.closest('[data-cart-item]');
            const quantity = Number.parseInt(item?.querySelector('[data-quantity-input]')?.value, 10) || 0;
            const unitPriceCents = Math.round((Number(checkbox.dataset.itemUnitPrice) || 0) * 100);

            return sum + unitPriceCents * quantity;
        }, 0);
        const selectedTotal = selectedTotalCents / 100;
        const hasSelection = selectedCount > 0;

        if (selectAll) {
            selectAll.checked = availableCheckboxes.length > 0 && availableCheckboxes.every(cb => cb.checked);
        }

        if (deleteButton) {
            deleteButton.disabled = !hasSelection;
        }

        if (checkoutButton) {
            checkoutButton.disabled = !hasSelection;
        }

        if (selectedItemsCount) {
            selectedItemsCount.textContent = String(selectedCount);
        }

        if (selectedItemsTotal) {
            selectedItemsTotal.innerHTML = formatMoney(selectedTotal);
        }

        if (selectedGrandTotal) {
            selectedGrandTotal.innerHTML = formatMoney(selectedTotal);
        }

        if (selectionSummary) {
            selectionSummary.classList.toggle('hidden', !hasSelection);
        }

        if (selectionEmptyMessage) {
            selectionEmptyMessage.classList.toggle('hidden', hasSelection);
        }

        if (selectionEmptyState) {
            selectionEmptyState.classList.toggle('hidden', hasSelection);
        }
    };

    // «Выбрать все» меняет только доступные варианты товаров.
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            checkboxes.forEach(cb => {
                if (!cb.disabled) cb.checked = e.target.checked;
            });
            updateSelectionState();
        });
    }

    // Снимаем «Выбрать все», если пользователь вручную снял один чекбокс.
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateSelectionState);
    });

    // Скрываем минус при количестве 1 и показываем его начиная с 2.
    const updateDecreaseButton = (input) => {
        const form = input.closest('form');
        const decreaseButton = form?.querySelector('[data-quantity-decrease]');
        const quantity = Number.parseInt(input.value, 10) || 1;

        if (!decreaseButton) {
            return;
        }

        decreaseButton.classList.toggle('hidden', quantity <= 1);
        decreaseButton.classList.toggle('flex', quantity > 1);
    };

    document.querySelectorAll('[data-quantity-input]').forEach((input) => {
        updateDecreaseButton(input);
        input.addEventListener('input', () => {
            updateDecreaseButton(input);
            updateSelectionState();
        });
        input.addEventListener('change', () => {
            updateDecreaseButton(input);
            updateSelectionState();
        });
    });

    // Сохраняем выбранные карточки при серверном обновлении количества и redirect.
    document.querySelectorAll('[data-cart-quantity-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-selected-cart-item]').forEach((input) => input.remove());

            checkboxes.forEach((checkbox) => {
                if (!checkbox.disabled && checkbox.checked) {
                    const selectedInput = document.createElement('input');
                    selectedInput.type = 'hidden';
                    selectedInput.name = 'selected_items[]';
                    selectedInput.value = checkbox.value;
                    selectedInput.dataset.selectedCartItem = 'true';
                    form.append(selectedInput);
                }
            });
        });
    });

    // Кнопки количества используют ту же форму PUT, что и ручной ввод.
    document.querySelectorAll('[data-quantity-decrease], [data-quantity-increase]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const form = button.closest('form');
                const input = form?.querySelector('input[name="quantity"]');

                if (!form || !input || input.disabled) {
                    return;
                }

                const currentValue = Number.parseInt(input.value, 10) || 1;
                const nextValue = button.hasAttribute('data-quantity-increase')
                    ? currentValue + 1
                    : Math.max(1, currentValue - 1);

                if (nextValue === currentValue) {
                    return;
                }

                input.value = nextValue;
                updateDecreaseButton(input);
                updateSelectionState();
                form.requestSubmit();
            });
        });

    updateSelectionState();
});
</script>