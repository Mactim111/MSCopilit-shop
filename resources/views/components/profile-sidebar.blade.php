@props(['active' => 'personal', 'tabMode' => false])

@php
    $items = [
        ['key' => 'personal', 'label' => 'Личные данные', 'href' => route('profile')],
        ['key' => 'bonuses', 'label' => 'Мои бонусы', 'href' => '#bonuses'],
        ['key' => 'orders', 'label' => 'Мои заказы', 'href' => route('profile.orders')],
        ['key' => 'notifications', 'label' => 'Настройки уведомлений', 'href' => '#notifications'],
        ['key' => 'store', 'label' => 'Любимый магазин', 'href' => '#store'],
        ['key' => 'addresses', 'label' => 'Адрес доставки', 'href' => '#addresses'],
        ['key' => 'favorites', 'label' => 'Избранное', 'href' => route('profile.favorites')],
        ['key' => 'reviews', 'label' => 'Мои отзывы', 'href' => route('profile.reviews')],
    ];
@endphp

<aside class="w-full shrink-0 p-1 md:w-[315px]">
    <nav class="overflow-hidden rounded-[10px] border border-gray-100 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.20)]"
         aria-label="Разделы профиля">
        <ul class="flex flex-col">
            @foreach($items as $item)
                @php
                    $isActive = $active === $item['key'];
                    $isLocalTab = $tabMode && !in_array($item['key'], ['orders', 'favorites', 'reviews'], true);
                @endphp
                <li>
                    <a href="{{ $item['href'] }}"
                       @if($isLocalTab) data-profile-tab="{{ $item['key'] }}" @endif
                       @if($isActive) aria-current="page" @endif
                       class="relative flex min-h-[57px] w-full items-center gap-3 border-b border-dashed border-gray-200 pl-[15px] pr-[10px] py-3 text-left text-[15px] text-[#231f20] transition-colors duration-200 {{ $isActive ? 'bg-[#f4f4f4] before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-[#dc092e]' : 'hover:bg-[#f4f4f4]' }}">
                        @include('components.profile.icons.' . $item['key'])
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
            <li class="flex items-center justify-between px-[10px] py-2">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="cursor-pointer text-[15px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]">
                        Выход
                    </button>
                </form>
                <a href="#"
                   class="text-[15px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]">
                    Удалить аккаунт
                </a>
            </li>
        </ul>
    </nav>
</aside>
