@props(['address'])

<article class="flex flex-col mt-[5px] gap-4 border-b border-dashed border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-start gap-4">
        <form action="{{ route('profile.address.default', $address) }}"
              method="POST"
              data-address-default-form
              class="shrink-0">
            @csrf
            @method('PATCH')
            <label class="flex h-full cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
                <input type="radio"
                       name="default_address"
                       value="{{ $address->id }}"
                       @checked($address->is_default)
                       aria-label="Сделать адресом по умолчанию"
                       class="peer sr-only"
                       data-address-default-radio>
                <span class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
            </label>
        </form>

        <div class="text-[15px] text-[#231f20]">
            <div class="font-semibold">{{ $address->fullAddress() }}</div>
            @if($address->is_default)
                <div class="text-[13px] font-normal text-[#7b7979]">Адрес по умолчанию</div>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 gap-5 pl-8 text-[15px] sm:pl-0">
        <button type="button"
                data-address-edit
                data-address-id="{{ $address->id }}"
                data-address-update-url="{{ route('profile.address.update', $address) }}"
                data-city="{{ $address->city }}"
                data-street="{{ $address->street }}"
                data-house="{{ $address->house }}"
                data-apartment="{{ $address->apartment }}"
                data-entrance="{{ $address->entrance }}"
                data-floor="{{ $address->floor }}"
                data-is-default="{{ $address->is_default ? '1' : '0' }}"
                class="cursor-pointer text-[#007eff] transition-colors duration-200 hover:text-[#0064cc]">
            Изменить
        </button>
        <form action="{{ route('profile.address.delete', $address) }}"
              method="POST"
              data-address-delete-form>
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="cursor-pointer text-[#007eff] transition-colors duration-200 hover:text-[#0064cc]">
                Удалить
            </button>
        </form>
    </div>
</article>
