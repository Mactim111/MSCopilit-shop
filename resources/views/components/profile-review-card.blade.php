@props(['review'])

@php
    $variant = $review->variant;
    $variantImage = $variant->images->first()?->url
        ?? asset('storage/assets/img/photos_will_appear_soon.jpg');
    $moderationStatuses = [
        \App\Models\Review::MODERATION_PENDING => [
            'label' => 'На модерации',
            'classes' => 'border-[#2c9cdd] bg-[#2c9cdd]',
        ],
        \App\Models\Review::MODERATION_APPROVED => [
            'label' => 'Одобрен',
            'classes' => 'border-[#8bc34a] bg-[#8bc34a]',
        ],
        \App\Models\Review::MODERATION_REJECTED => [
            'label' => 'Отклонён',
            'classes' => 'border-[#dc032e] bg-[#dc032e]',
        ],
    ];
    $reviewStatus = $moderationStatuses[$review->moderation_status];
@endphp

<article class="cabinet-reviews__card w-full rounded-[10px] border border-[#e5e5e5] px-6 py-6 md:px-8">
    <div class="flex flex-col">
        <header class="mb-4 flex items-start justify-between gap-4 border-b border-dashed border-[#e5e5e5] pb-4">
            <div class="flex min-w-0 items-start gap-4">
                <a href="{{ route('catalog.variant', $variant) }}"
                   class="h-[80px] w-[80px] shrink-0 overflow-hidden rounded-[2px] bg-white"
                   aria-label="Перейти к товару {{ $variant->title }}">
                    <img src="{{ $variantImage }}"
                         alt="{{ $variant->title }}"
                         class="h-full w-full object-contain">
                </a>

                <div class="flex min-w-0 flex-col items-start text-left">
                    <span class="mb-[9px] inline-flex min-h-[20px] items-center justify-center rounded-[4px] border px-[7px] py-[2px] text-center text-[12px] font-normal leading-none text-white {{ $reviewStatus['classes'] }}">
                        {{ $reviewStatus['label'] }}
                    </span>
                    <a href="{{ route('catalog.variant', $variant) }}"
                       class="mb-[3px] max-w-full text-[15px] font-bold text-black hover:underline">
                        {{ $variant->title }}
                    </a>

                    <div class="flex flex-wrap items-center gap-[9px] text-left">
                        <div class="flex shrink-0 items-center gap-[2px]">
                            @for($star = 1; $star <= 5; $star++)
                                <svg class="h-4 w-4 {{ $star <= $review->rating ? 'fill-[#ffb000]' : 'fill-white stroke-[#8c8c8c]' }}"
                                     viewBox="0 0 24 24"
                                     aria-hidden="true">
                                    <path stroke-width="1.5" d="M12 .587l3.668 7.568L24 9.748l-6 5.848L19.335 24 12 19.897 4.665 24 6 15.596 0 9.748l8.332-1.593z"/>
                                </svg>
                            @endfor
                            <span class="ml-1 text-[15px] font-bold text-[#ffb000]">
                                {{ number_format($review->rating, 1) }}
                            </span>
                        </div>
                        <span class="text-[15px] text-[#8c8c8c]" aria-hidden="true">|</span>
                        <time class="text-[15px] text-[#8c8c8c]"
                              datetime="{{ $review->created_at->toIso8601String() }}">
                            {{ $review->created_at->locale('ru')->diffForHumans() }}
                        </time>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 text-[#8c8c8c]" aria-label="Оценки отзыва">
                <span class="flex items-center gap-[5px]" aria-label="Полезный отзыв: {{ $review->likes_count }}">
                    <span class="text-[14px]">{{ $review->likes_count }}</span>
                    <span class="flex h-5 w-5 items-center justify-center">
                        @include('products.icons.r-likes__like')
                    </span>
                </span>
                <span class="flex items-center gap-[5px]" aria-label="Не полезный отзыв: {{ $review->dislikes_count }}">
                    <span class="text-[14px]">{{ $review->dislikes_count }}</span>
                    <span class="flex h-5 w-5 items-center justify-center">
                        @include('products.icons.r-likes__dislike')
                    </span>
                </span>
            </div>
        </header>

        <div class="w-full pb-[2px] pt-[1px] text-[15px] text-black">
            <p class="mb-[8px]">
                <strong>Преимущества:</strong>
                {{ $review->advantages }}
            </p>
            <p class="mb-[8px]">
                <strong>Недостатки:</strong>
                {{ $review->disadvantages }}
            </p>
            <p class="mb-[13px]">
                <strong>Комментарий:</strong>
                {{ $review->comment }}
            </p>
        </div>

        @if($review->images->isNotEmpty())
            <div class="mb-[17px] flex flex-wrap gap-2 text-left">
                @foreach($review->images as $image)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                       target="_blank"
                       rel="noopener"
                       class="block h-[80px] w-[80px] overflow-hidden rounded-lg bg-gray-100">
                        @if($image->media_type === 'video')
                            <video class="h-full w-full object-cover"
                                   preload="metadata"
                                   aria-label="Видео к отзыву">
                                <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}">
                            </video>
                        @else
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                                 alt="Фотография к отзыву"
                                 class="h-full w-full object-cover">
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        @if($review->addition)
            @php($additionStatus = $moderationStatuses[$review->addition_moderation_status])
            <section class="border-t border-dashed border-gray-300 pl-6 pt-4 pb-4 text-left">
                <div class="mb-[10px] flex flex-wrap items-center gap-x-[10px] gap-y-2">
                    <time class="text-[15px] text-[#8c8c8c]"
                          @if($review->addition_updated_at) datetime="{{ $review->addition_updated_at->toIso8601String() }}" @endif>
                        Дополнен
                        {{ $review->addition_updated_at?->locale('ru')->diffForHumans() ?? '—' }}
                    </time>
                    <span class="inline-flex min-h-[20px] items-center justify-center rounded-[4px] border px-[7px] py-[2px] text-center text-[12px] font-normal leading-none text-white {{ $additionStatus['classes'] }}">
                        {{ $additionStatus['label'] }}
                    </span>
                </div>
                <div class="w-full text-[15px] text-black">
                    {{ $review->addition }}
                </div>
            </section>
        @endif

        @if(auth()->id() === $review->user_id)
            <div class="flex w-full items-center justify-between border-t border-dashed border-gray-300 pt-[21px] text-left">
                @if($review->addition === null)
                    <a href="{{ route('catalog.variant.reviews', $variant) }}"
                       data-review-form="addition"
                       data-review-id="{{ $review->id }}"
                       data-form-action="{{ route('reviews.addition', $review) }}"
                       data-product-title="{{ $variant->title }}"
                       data-product-image="{{ $variantImage }}"
                       class="text-[13px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                        Дополнить
                    </a>
                @else
                    <span class="text-[13px] text-gray-500">Отзыв дополнен</span>
                @endif

                <form action="{{ route('reviews.destroy', $review) }}"
                      method="POST"
                      data-review-delete-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="cursor-pointer text-[13px] text-[#DC092E] hover:text-[#a80723]">
                        Удалить
                    </button>
                </form>
            </div>
        @endif

        {{-- Ответ магазина и разделитель существуют только при наличии ответа. --}}
        @if($review->reply)
            <section class="w-full border-t border-dashed border-gray-300 mt-[25px] pt-[4px] pl-5 text-left">
                <div class="mb-[5px] flex min-h-[38px] items-center pl-1 pt-[7px]">
                    <span class="flex items-center pt-[7px]">
                        @include('products.icons.my-shop')
                    </span>
                    <time class="text-[15px] pt-[7px] text-[#8c8c8c]"
                          datetime="{{ $review->reply->created_at->toIso8601String() }}">
                        {{ $review->reply->created_at->locale('ru')->diffForHumans() }}
                    </time>
                </div>
                <div class="pb-[2px] pl-1">
                    <p class="mb-[13px] text-[15px] text-black">
                        {{ $review->reply->body }}
                    </p>
                </div>
            </section>
        @endif
    </div>
</article>