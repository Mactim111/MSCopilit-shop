<div id="orders-list" class="space-y-4">
    @forelse($orders as $order)
        <x-profile-order-list-card :order="$order" />
    @empty
        <div class="text-left -mt-4">
            <p class="text-[15px] font-semibold text-black">
                У Вас пока нет заказов
            </p>
            <a href="{{ route('catalog.index') }}"
               class="mt-4 inline-block text-[15px] text-[#007eff] hover:text-[#0064cc] transition-all duration-200">
                Перейти в каталог
            </a>
        </div>
    @endforelse
</div>