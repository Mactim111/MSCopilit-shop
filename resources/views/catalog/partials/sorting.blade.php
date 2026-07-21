@php
    $sort = request('sort', 'popular');

    $sortOptions = [
        'popular'     => 'По популярности',
        'price_asc'   => 'Сначала дешевле',
        'price_desc'  => 'Сначала дороже',
        'new'         => 'По новизне',
        'discount'    => 'По проценту скидки',
        'rating'      => 'По рейтингу',
    ];
@endphp

<div class="relative w-[238px] mb-[18px]">

    {{-- Строка сортировки --}}
    <div id="sort-toggle" class="flex items-center cursor-pointer select-none">

        <span class="text-[14px] text-[#231F20] mr-[8px]">Сортировка:</span>

        <span id="sort-current" class="text-[14px] text-[#007EFF]">
            {{ $sortOptions[$sort] }}
        </span>

        {{-- Блок иконки (кликабельный) --}}
        <button id="sort-icon"
                type="button"
                class="ml-[8px] w-[8px] h-[4px] flex items-center justify-center">
            @include('products.icons.sort-down')
        </button>

    </div>

    {{-- Выпадающий список --}}
    <div id="sort-dropdown"
         class="absolute left-0 w-[218px] h-[225] bg-white shadow-lg rounded-md
                text-[15px] text-[#231F20]
                p-[15px] mt-[6px] hidden z-50 shadow-md">

        @foreach($sortOptions as $value => $label)
            <div class="sort-option py-[5px] cursor-pointer hover:text-[#DC092E]"
                 data-value="{{ $value }}">
                {{ $label }}
            </div>
        @endforeach

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const toggle = document.getElementById('sort-toggle');
        const dropdown = document.getElementById('sort-dropdown');
        const current = document.getElementById('sort-current');
        const icon = document.getElementById('sort-icon');

        const iconDown = `{!! trim(preg_replace('/\s+/', ' ', view('products.icons.sort-down')->render())) !!}`;
        const iconUp   = `{!! trim(preg_replace('/\s+/', ' ', view('products.icons.sort-up')->render())) !!}`;

        function setIcon(isOpen) {
            if (isOpen) {
                icon.innerHTML = iconUp;
                icon.style.marginTop = "2px";
                icon.style.marginBottom = "0";
            } else {
                icon.innerHTML = iconDown;
                icon.style.marginTop = "0";
                icon.style.marginBottom = "0px";
            }
        }

        toggle.addEventListener('click', () => {
            dropdown.classList.toggle('hidden');
            setIcon(!dropdown.classList.contains('hidden'));
        });

        document.querySelectorAll('.sort-option').forEach(option => {
            option.addEventListener('click', () => {
                const value = option.dataset.value;
                const label = option.textContent.trim();

                current.textContent = label;

                dropdown.classList.add('hidden');
                setIcon(false);

                const url = new URL(window.location.href);
                url.searchParams.set('sort', value);
                window.location.href = url.toString();
            });
        });

        document.addEventListener('click', (e) => {
            if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
                setIcon(false);
            }
        });

    });
</script>




