@if($variants->hasMorePages())
    <div id="favorites-show-more-wrapper"
         data-current-page="{{ $variants->currentPage() }}"
         data-last-page="{{ $variants->lastPage() }}"
         class="w-[1152px] h-[42px] px-[30px] py-[11px] mb-[16px]
                border border-gray-200 rounded-lg shadow-md relative z-20
                flex items-center justify-center cursor-pointer select-none bg-white">
        <button type="button"
                data-favorites-show-more
                class="text-[15px] text-[#007EFF] hover:text-[#0064cc] transition-all duration-200 cursor-pointer">
            Показать ещё
        </button>
    </div>
@endif
