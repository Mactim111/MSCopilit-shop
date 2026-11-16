@props(['comment'])

@if(filled($comment))
    <section class="mt-5 rounded-[10px] border border-[#e5e5e5] bg-white px-5 py-[20px] sm:px-[38px]">
        <h2 class="mb-3 text-[18px] font-bold text-black">Комментарий к заказу</h2>
        <p class="whitespace-pre-wrap break-words text-[15px] text-black">{{ $comment }}</p>
    </section>
@endif
