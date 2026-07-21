<div id="pagination-block"
     class="w-full h-[42px] border border-gray-200 rounded-lg shadow-md
            px-[30px] pt-[13px] pb-[9px] bg-white flex items-center justify-center text-[15px]">

    <div class="relative flex items-center justify-center gap-[36px] h-full">

        {{-- Назад --}}
        @if($variants->onFirstPage())
            <span class="text-[#C4C4C4] flex items-center">
                @include('products/icons.chevron-left-thin')
            </span>
        @else
            <a href="{{ $variants->previousPageUrl() }}" class="text-[#007EFF] flex items-center">
                @include('products/icons.chevron-left-thin')
            </a>
        @endif

        @php
            $current = $variants->currentPage();
            $last = $variants->lastPage();
            $start = max(1, $current - 1);
            $end = min($last, $start + 2);
        @endphp

        {{-- Первая страница всегда --}}
        @if($start > 1)
            <a href="{{ $variants->url(1) }}" class="text-[#007EFF]">
                1
            </a>

            @if($start > 2)
                <span>...</span>
            @endif
        @endif

        {{-- Окно вокруг текущей --}}
        @for($i = $start; $i <= $end; $i++)
            @if($i == $current)
                <span class="relative font-bold">
                    {{ $i }}
                    {{-- подчёркивание по нижней границе ВНУТРЕННЕГО блока --}}
                    <span class="pointer-events-none absolute left-[-4px] right-[-4px] bottom-[-2px] h-[2px] bg-red-600"></span>
                </span>
            @else
                <a href="{{ $variants->url($i) }}" class="text-[#007EFF]">
                    {{ $i }}
                </a>
            @endif
        @endfor

        {{-- Последняя страница --}}
        @if($end < $last)
            @if($end < $last - 1)
                <span>...</span>
            @endif

            @if($last == $current)
                <span class="relative font-bold">
                    {{ $last }}
                    <span class="pointer-events-none absolute left-[-4px] right-[-4px] bottom-[-2px] h-[2px] bg-red-600"></span>
                </span>
            @else
                <a href="{{ $variants->url($last) }}" class="text-[#007EFF]">
                    {{ $last }}
                </a>
            @endif
        @endif

        {{-- Вперёд --}}
        @if($variants->currentPage() < $last)
            <a href="{{ $variants->nextPageUrl() }}" class="text-[#007EFF] flex items-center">
                @include('products/icons.chevron-right-thin')
            </a>
        @else
            <span class="text-[#C4C4C4] flex items-center">
                @include('products/icons.chevron-right-thin')
            </span>
        @endif

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const pagination = document.getElementById('pagination-block');
        if (!pagination) return;

        pagination.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            e.preventDefault();
            loadPage(link.href);
        });

    });
</script>

