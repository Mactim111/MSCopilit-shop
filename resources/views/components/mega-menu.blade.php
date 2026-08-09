<div id="mega-menu"
     class="hidden fixed left-0 right-0 z-40 bg-white
        overflow-y-auto no-scrollbars">

    <div class="mx-auto max-w-[1500px] flex">

        {{-- ЛЕВАЯ КОЛОНКА --}}
        <div class="w-3/12">
            <ul class="py-[4px] space-y-1">

                {{-- АКЦИИ --}}
                
                    <li class="overflow-visible">
                        <a href="#"
                            class="w-full flex items-center justify-between px-5 py-3 text-[15px] hover:border-gray-200 cursor-pointer
                                hover:shadow-[0_4px_10px_-2px_rgba(0,0,0,0.25)] rounded-lg transition-all duration-150 border border-transparent" data-group="actions">
                            <span class="flex items-center gap-3">
                                <img src="{{ asset('storage/assets/img/actions.png') }}"
                                    class="w-[54px] h-[36px] object-cover rounded"
                                    alt="Акции">
                                <span>Акции</span>
                            </span>
                            @include('admin.svg.chevron-right', ['class' => 'w-[7px] h-[14px] text-gray-400'])
                        </a>
                    </li>
                

                {{-- ГРУППЫ КАТЕГОРИЙ --}}
                @foreach($categoryGroups as $group)
                    <li>
                        <a href="{{ route('catalog.group', $group) }}"
                            class="w-full flex items-center justify-between px-5 py-3 text-[15px] hover:border-gray-200 cursor-pointer
                                hover:shadow-[0_4px_10px_-2px_rgba(0,0,0,0.25)] rounded-lg transition-all duration-150 border border-transparent" data-group="group-{{ $group->id }}">
                            <span class="flex items-center gap-3">
                                <img src="{{ asset($group->image) }}"
                                     class="w-[54px] h-[36px] object-cover rounded"
                                     alt="{{ $group->title }}">
                                <span>{{ $group->title }}</span>
                            </span>
                            @include('admin.svg.chevron-right', ['class' => 'w-[7px] h-[14px] text-gray-400'])
                        </a>
                    </li>
                @endforeach

            </ul>
        </div>

        {{-- ПРАВАЯ КОЛОНКА --}}
        <div class="w-9/12 p-8">

            {{-- АКЦИИ --}}
            <div class="mega-panel" data-panel="actions">
                @include('actions.placeholder')
            </div>

            {{-- ПАНЕЛИ ДЛЯ ГРУПП --}}
            @foreach($categoryGroups as $group)
                <div class="mega-panel hidden" data-panel="group-{{ $group->id }}">
                    <h3 class="text-[20px] font-semibold mb-6">{{ $group->title }}</h3>

                    <div class="columns-3 gap-10 text-[16px] leading-[1.4]">

                        @foreach($group->children as $category)
                            <div class="break-inside-avoid mb-6">

                                {{-- Заголовок подгруппы (КАТЕГОРИЯ) --}}
                                {{-- FIX: заменили categories.show на catalog.category --}}
                                <a href="{{ route('catalog.category', [$group->slug, $category->slug]) }}"
                                   class="block text-[17px] font-semibold mb-2 hover:text-red-600">
                                    {{ $category->title }}
                                </a>

                                {{-- Список подкатегорий --}}
                                @php
                                    $children = $category->children;
                                    $visible = $children->take(9);
                                    $hidden = $children->slice(9);
                                @endphp

                                <ul class="space-y-1" data-subcolumn="{{ $category->id }}">

                                    @foreach($visible as $subcategory)
                                        <li>
                                            {{-- FIX: заменили categories.show на catalog.subcategory --}}
                                            <a href="{{ route('catalog.subcategory', [$group->slug, $category->slug, $subcategory->slug]) }}"
                                               class="hover:text-red-600">
                                                {{ $subcategory->title }}
                                            </a>
                                        </li>
                                    @endforeach

                                    @if($hidden->isNotEmpty())
                                        <li>
                                            <button type="button"
                                                    class="text-sky-600 hover:underline text-[14px]"
                                                    data-toggle-more="{{ $subcategory->id }}">
                                                Показать все
                                            </button>
                                        </li>

                                        <div class="hidden" data-more-list="{{ $subcategory->id }}">
                                            @foreach($hidden as $category)
                                                <li>
                                                    {{-- FIX: заменили categories.show на catalog.subcategory --}}
                                                    <a href="{{ route('catalog.subcategory', [$group->slug, $category->slug, $subcategory->slug]) }}"
                                                       class="hover:text-red-600">
                                                        {{ $category->title }}
                                                    </a>
                                                </li>
                                            @endforeach

                                            <li>
                                                <button type="button"
                                                        class="text-sky-600 hover:underline text-[14px]"
                                                        data-toggle-less="{{ $subcategory->id }}">
                                                    Скрыть
                                                </button>
                                            </li>
                                        </div>
                                    @endif

                                </ul>

                            </div>
                        @endforeach

                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>
