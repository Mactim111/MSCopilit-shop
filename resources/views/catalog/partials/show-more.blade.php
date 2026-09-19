@if($variants->hasMorePages())
<div id="show-more-wrapper"
        data-current-page="{{ $variants->currentPage() }}"
        data-last-page="{{ $variants->lastPage() }}"
     class="w-[1152px] h-[42px] px-[30px] py-[11px] mb-[16px]
            border border-gray-200 rounded-lg shadow-md
            relative z-20 flex items-center justify-center cursor-pointer select-none bg-white">
    {{-- вместо ID , который может дублироваться при AJAX‑замене HTML, при этом может быть так, что обработчики на ID теряются и 
    ID ломает делегирование событий, для кнопки устанавливаем атрибут, работающий как «маркер», чтобы найти кнопку через closest() --}}
    <button data-show-more
            class="text-[15px] text-[#007EFF] hover:text-[#0064cc] transition-all duration-200 cursor-pointer">
        Показать ещё
    </button>

</div>
@endif
