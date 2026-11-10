@props(['address'])

<article class="flex flex-col gap-4 border-b border-dashed border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-start gap-3">
        <form action="{{ route('profile.address.default', $address) }}"
              method="POST"
              data-address-default-form
              class="shrink-0">
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_default" value="0">
            <label class="mt-1 flex cursor-pointer items-center">
                <input type="checkbox"
                       name="is_default"
                       value="1"
                       @checked($address->is_default)
                       aria-label="Сделать адресом по умолчанию"
                       class="h-5 w-5 cursor-pointer accent-[#dc092e]"
                       data-address-default-checkbox>
            </label>
        </form>

        <div class="text-[15px] text-[#231f20]">
            <div class="font-semibold">{{ $address->fullAddress() }}</div>
            @if($address->is_default)
                <div class="mt-1 text-[13px] font-normal text-[#7b7979]">Адрес по умолчанию</div>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 gap-5 pl-8 text-[15px] sm:pl-0">
        <a href="#"
           class="text-[#007eff] transition-colors duration-200 hover:text-[#0064cc]">
            Изменить
        </a>
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
