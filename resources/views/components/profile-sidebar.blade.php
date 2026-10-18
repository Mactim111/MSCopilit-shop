@props(['active' => 'personal', 'tabMode' => false])

@php
    $items = [
        ['key' => 'personal', 'label' => 'Личные данные', 'href' => route('profile'), 'icon' => 'user'],
        ['key' => 'bonuses', 'label' => 'Мои бонусы', 'href' => '#bonuses', 'icon' => 'gift'],
        ['key' => 'orders', 'label' => 'Мои заказы', 'href' => route('profile.orders'), 'icon' => 'box'],
        ['key' => 'notifications', 'label' => 'Настройки уведомлений', 'href' => '#notifications', 'icon' => 'settings'],
        ['key' => 'store', 'label' => 'Любимый магазин', 'href' => '#store', 'icon' => 'store'],
        ['key' => 'addresses', 'label' => 'Адрес доставки', 'href' => '#addresses', 'icon' => 'pin'],
        ['key' => 'favorites', 'label' => 'Избранное', 'href' => route('profile.favorites'), 'icon' => 'heart'],
        ['key' => 'reviews', 'label' => 'Мои отзывы', 'href' => '#reviews', 'icon' => 'review'],
    ];
@endphp

<aside class="w-full shrink-0 p-1 md:w-[315px]">
    <nav class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.20)]"
         aria-label="Разделы профиля">
        <ul class="flex flex-col">
            @foreach($items as $item)
                @php
                    $isActive = $active === $item['key'];
                    $isLocalTab = $tabMode && !in_array($item['key'], ['orders', 'favorites'], true);
                @endphp
                <li>
                    <a href="{{ $item['href'] }}"
                       @if($isLocalTab) data-profile-tab="{{ $item['key'] }}" @endif
                       @if($isActive) aria-current="page" @endif
                       class="relative flex min-h-[43px] items-center gap-3 border-b border-dashed border-gray-200 px-[10px] py-[10px] text-left text-[15px] text-[#231f20] transition-colors duration-200 {{ $isActive ? 'bg-[#f4f4f4] before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-[#dc092e]' : 'hover:bg-[#f4f4f4]' }}">
                        <svg class="h-[22px] w-[22px] shrink-0 text-[#595758]"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            @if($item['icon'] === 'user')
                                <circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                            @elseif($item['icon'] === 'gift')
                                <path d="M3 10h18v11H3zM2 6h20v4H2zM12 6v15M12 6H7.5a2.5 2.5 0 1 1 2.2-3.7L12 6Zm0 0h4.5a2.5 2.5 0 1 0-2.2-3.7L12 6Z"/>
                            @elseif($item['icon'] === 'box')
                                <path d="m3 7 9-4 9 4v10l-9 4-9-4zM3 7l9 4 9-4M12 11v10M8 5l9 4"/>
                            @elseif($item['icon'] === 'settings')
                                <circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2A1.7 1.7 0 0 0 8 17.4l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4.2a1.7 1.7 0 0 1 0-3.4h.2A1.7 1.7 0 0 0 5.6 6.3l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2v-.2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.4 2.6Z"/>
                            @elseif($item['icon'] === 'store')
                                <path d="M3 10v11h18V10M2 3h20l-2 7H4L2 3ZM9 21v-7h6v7"/>
                            @elseif($item['icon'] === 'pin')
                                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>
                            @elseif($item['icon'] === 'heart')
                                <path d="M20.8 8.7c0 5.3-8.8 11-8.8 11s-8.8-5.7-8.8-11A4.7 4.7 0 0 1 12 6.2a4.7 4.7 0 0 1 8.8 2.5Z"/>
                            @else
                                <path d="M3 12h3l2-7 4 14 3-10 2 3h4"/>
                            @endif
                        </svg>
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
