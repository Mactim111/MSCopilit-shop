@php
    use App\Models\CartItem;
    use Illuminate\Support\Facades\Auth;
    
@endphp

@if($product_variants_slider->count() >= 5)

{{-- у 5 ЭЛЕМЕНТ под слайдером mb-[70px] НО! оно так на странице не выглядит! по этому у себя применили mb-[35px] --}}
<div class="w-full bg-white mb-[35px]">
    <div class="max-w-[1500px] mx-auto relative">

        {{-- Кнопки навигации --}}
        <button type="button"
            class="js-swiper-prev absolute left-[-16px] top-[45%] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        <button type="button"
            class="js-swiper-next absolute right-[-16px] top-[45%] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- SWIPER --}}
        {{-- data-space="6" + px-[6px] дадут суммарный gap 18px --}}
        <div class="js-swiper swiper w-full overflow-hidden"
            data-slides="5"
            data-space="6"
            data-loop="true"
            data-navigation="true"
            data-pagination="true"
            data-group="1">

            <div class="swiper-wrapper py-[10px]">
                @foreach($product_variants_slider as $variant)
                    {{-- 
                       px-[6px] возвращает крайние слайды ближе к кнопкам.
                       Теперь кнопка будет "резать" край карточки, а не висеть отдельно.
                    --}}
                    <div class="swiper-slide flex justify-center px-[4px] py-[6px]">
                        
                        {{-- 
                           ТЕНЬ (Shadow):
                           - Обычная: shadow-[0_2px_8px_rgba(0,0,0,0.1)] (едва заметная)
                           - Ховер: shadow-[0_4px_14px_rgba(0,0,0,0.22)] (тоньше и светлее, чем была)
                        --}}
                        <div class="w-full h-[470px] border border-gray-100 rounded-xl p-[12px] bg-white 
                                    shadow-[0_2px_8px_rgba(0,0,0,0.1)]
                                    transition-all duration-300
                                    hover:shadow-[0_4px_14px_rgba(0,0,0,0.22)]
                                    hover:border-gray-200
                                    focus-visible:shadow-[0_4px_14px_rgba(0,0,0,0.22)]
                                    focus-visible:border-gray-200
                                    focus-visible:outline-none flex flex-col group">
                                
                            {{-- Верхний блок --}}
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex gap-2">
                                    @foreach($variant->labels as $label)
                                        <x-dynamic-component :component="'labels.' . $label->component" />
                                    @endforeach
                                </div>
                                <button class="favorite-toggle" data-id="{{ $variant->article }}">
                                    @include('products.icons.heart-outline')
                                </button>
                            </div>

                            {{-- Фото --}}
                            <a href="{{ route('catalog.variant', $variant->slug) }}" class="mb-3 block cursor-pointer">
                                <div class="w-full aspect-square max-w-[246px] mx-auto rounded-lg overflow-hidden bg-white flex items-center justify-center">
                                    <img src="{{ $variant->mainImage() }}" alt="{{ $variant->title }}"
                                         class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                                </div>
                            </a>

                            {{-- Название --}}
                            <a href="{{ route('catalog.variant', $variant->slug) }}" class="mb-3 block grow cursor-pointer">
                                <h2 class="text-[15px] font-bold line-clamp-2 h-[42px] leading-tight text-[#231F20] hover:text-red-600 transition">
                                    {{ $variant->title }}
                                </h2>
                            </a>

                            {{-- Цена + кнопка --}}
                            <div class="grid grid-cols-2 gap-4 items-start">

                                <div class="min-h-[50px] flex flex-col justify-start">
                                    <div class="text-[30px] font-bold text-gray-900">
                                        {!! $variant->formattedPrice(30, 19) !!}
                                    </div>

                                    @if($variant->old_price > 0)
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400 text-[15px] line-through decoration-gray-500 font-semibold">
                                            {!! $variant->formattedOldPrice(15, 15) !!}
                                        </span>
                                        @if($variant->discount_percent)
                                        <span class="text-red-600 text-[15px] font-bold">
                                            -{{ $variant->discount_percent }}%
                                        </span>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                @php
                                $inCart = CartItem::where('user_id', Auth::id())
                                ->where('product_variant_id', $variant->id)
                                ->exists();
                                @endphp

                                @if($inCart)
                                <a href="{{ route('cart.index') }}"
                                    class="flex items-center justify-center w-full h-[40px] bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors 
                                    text-[15px] cursor-pointer">
                                    В корзине
                                </a>
                                @else
                                <div class="text-right">
                                    <form action="{{ route('cart.add', $variant) }}" method="POST" class="mx-auto">
                                        @csrf
                                        <button class="w-full h-[40px] bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors text-[15px]
                                        cursor-pointer">
                                            В корзину
                                        </button>
                                    </form>
                                </div>
                                @endif

                            </div>

                            {{-- Рейтинг --}}
                            <div class="flex items-center gap-4 text-sm text-gray-600 mt-1">
                                <div class="flex items-center gap-1">
                                    @include('products.icons.star')
                                    <span class="font-semibold text-gray-900">
                                        {{ number_format($variant->product->rating, 1) }}
                                    </span>
                                </div>
                                <a href="#" class="flex items-center gap-1 text-gray-500 hover:text-gray-700">
                                    @include('products.icons.message')
                                    <span>
                                        {{ number_format($variant->product->reviews_count, 0, '.', ' ') }}
                                        {{ $variant->product->reviews_label }}
                                    </span>
                                </a>
                            </div>
                            {{-- <div class="flex items-center gap-3 text-sm pt-3">
                                <div class="flex items-center gap-1">
                                    <span class="text-yellow-400">@include('products.icons.star')</span>
                                    <span class="font-bold text-[#231F20]">4.8</span>
                                </div>
                                <div class="text-gray-400 flex items-center gap-1 text-[13px]">
                                    @include('products.icons.message')
                                    <span>12 отзывов</span>
                                </div>
                            </div> --}}

                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <div class="js-swiper-pagination rv-pagination"></div>

    </div>
</div>

@endif