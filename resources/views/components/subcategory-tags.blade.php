@php
    $visibleCount = 16;
@endphp

@if(isset($subcategoryTags) && $subcategoryTags->count() > 0)
<div id="tags-block" class="w-full bg-white rounded-lg mb-6 overflow-hidden p-[4px]">

    <div class="flex flex-wrap gap-[12px] items-end">

        @foreach($subcategoryTags as $index => $tag)
            <div class="js-tag-item {{ $index >= $visibleCount ? 'hidden' : '' }}">
                <a href="{{ $tag['url'] }}"
                   class="h-[32px] px-[12px] pt-[5px] pb-[7px] rounded-[5px]
                          text-[15px] leading-none inline-flex items-center justify-center
                          transition-all duration-300 cursor-pointer
                          {{ ($tag['active'] ?? false)
                              ? 'bg-white border border-[#231F20] shadow-sm'
                              : 'bg-[#F4F4F4] border border-transparent
                                 hover:bg-white hover:border-gray-100
                                 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]'
                          }}
                          {{ ($tag['type'] ?? '') === 'toggle' ? 'italic' : '' }}
                ">
                    {{-- Для toggle добавляем визуальный маркер --}}
                    @if(($tag['type'] ?? '') === 'toggle')
                        <span class="mr-[4px] text-[#DC092E]">✓</span>
                    @endif
                    {{ $tag['label'] }}
                </a>
            </div>
        @endforeach

        @if($subcategoryTags->count() > ($visibleCount ?? 16))
            <button id="tags-toggle"
                    data-state="collapsed"
                    class="h-[32px] pb-[2px] pl-[8px] text-[14px] text-[#007EEF]
                           hover:text-[#005bb5] transition-colors cursor-pointer outline-none">
                Еще
            </button>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn  = document.getElementById('tags-toggle');
    if (!toggleBtn) return;

    const allItems    = document.querySelectorAll('.js-tag-item');
    const hiddenItems = document.querySelectorAll('.js-tag-item.hidden');

    toggleBtn.addEventListener('click', () => {
        const collapsed = toggleBtn.dataset.state === 'collapsed';

        hiddenItems.forEach(item => {
            item.classList.toggle('hidden', !collapsed);
            item.classList.toggle('flex', collapsed);
        });

        toggleBtn.innerText     = collapsed ? 'Скрыть' : 'Еще';
        toggleBtn.dataset.state = collapsed ? 'expanded' : 'collapsed';

        if (!collapsed) {
            document.getElementById('tags-block')
                .scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
});
</script>
@endif