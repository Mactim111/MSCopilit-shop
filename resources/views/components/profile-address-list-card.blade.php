@props(['address'])

<div class="flex justify-between mt-[5px] pb-3 border-b border-dashed border-gray-200">

    <label class="flex h-full cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
        <input class="peer sr-only" type="radio" name="adress" value="adress"
            @checked(old('adress', $address->id) === $address->id) required data-profile-editable>
        <span class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
        <div class="pl-2.5 text-[15px]">{{ $address->full_address }}</div>
    </label>

    <div class="text-[15px] text-[#007eff] hover:text-[#0064cc] transition-all duration-200">
        <a href="#" class="mr-5 ">
            Изменить
        </a>
        <a href="#">
            Удалить
        </a>
    </div>
    
</div>