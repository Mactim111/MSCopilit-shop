<div id="addresses-list" class="space-y-4">
    @forelse($addresses as $address)
    <x-profile-address-list-card :address="$address" />
    @empty
        <div class="text-left mb-4">
            <p class="text-[15px] font-semibold text-black -mt-4">
                Добавьте адрес доставки, чтобы не заполнять его каждый раз при оформлении заказа.
            </p>
        </div>
    @endforelse
        <button type="button"
            data-address-open
            class="inline-block cursor-pointer text-[15px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
            Добавить адрес
        </button>
</div>
