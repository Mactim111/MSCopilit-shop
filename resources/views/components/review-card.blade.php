@props(['review', 'isAuthenticated' => false])

@php
    $authorInitial = \Illuminate\Support\Str::substr($review->user->name, 0, 1);
@endphp

<article class="border-b border-[#f2f2f2] pt-6 text-left"
         data-review-card
         data-review-id="{{ $review->id }}">
    {{-- Заголовок: автор и реакции в первой строке; рейтинг, вариант и фото ниже. --}}
    <header class="mb-[14px] flex flex-col gap-[15px]">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-[15px]">
                <div class="flex h-[45px] w-[45px] shrink-0 items-center justify-center rounded-full bg-[#f2f2f2] text-[15px] text-[#8c8c8c]">
                    {{ $authorInitial }}
                </div>
                <div class="flex flex-col text-left">
                    <span class="mb-1 text-[15px] font-bold text-black">
                        {{ $review->user->name }}
                    </span>
                    <time class="text-[15px] text-[#8c8c8c]"
                          datetime="{{ $review->created_at->toIso8601String() }}">
                        {{ $review->created_at->locale('ru')->diffForHumans() }}
                    </time>
                </div>
            </div>

            <div class="flex h-[45px] w-[90px] shrink-0 items-center justify-between">
                <div class="flex items-center">
                    <span class="min-w-[14px] text-[14px] text-[#8c8c8c]"
                          data-vote-count="like">{{ $review->likes_count }}</span>
                    @if($isAuthenticated)
                        <button type="button"
                                data-review-vote
                                data-type="like"
                                data-url="{{ route('reviews.vote', $review) }}"
                                class="ml-[6px] flex h-6 w-6 items-center justify-center text-[#8c8c8c]"
                                aria-label="Полезный отзыв">
                            @include('products.icons.r-likes__like')
                        </button>
                    @else
                        <span class="ml-[6px] flex h-6 w-6 items-center justify-center text-[#8c8c8c]"
                              aria-label="Полезный отзыв">
                            @include('products.icons.r-likes__like')
                        </span>
                    @endif
                </div>

                <div class="flex items-center">
                    <span class="min-w-[14px] text-[14px] text-[#8c8c8c]"
                          data-vote-count="dislike">{{ $review->dislikes_count }}</span>
                    @if($isAuthenticated)
                        <button type="button"
                                data-review-vote
                                data-type="dislike"
                                data-url="{{ route('reviews.vote', $review) }}"
                                class="ml-[6px] flex h-6 w-6 items-center justify-center text-[#8c8c8c]"
                                aria-label="Не полезный отзыв">
                            @include('products.icons.r-likes__dislike')
                        </button>
                    @else
                        <span class="ml-[6px] flex h-6 w-6 items-center justify-center text-[#8c8c8c]"
                              aria-label="Не полезный отзыв">
                            @include('products.icons.r-likes__dislike')
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex min-w-0 items-start gap-[11px] text-left">
            <div class="flex shrink-0 items-center gap-[2px] pt-[2px]">
                @for($star = 1; $star <= 5; $star++)
                    <svg class="h-[15px] w-[15px] {{ $star <= $review->rating ? 'fill-[#ffb000]' : 'fill-white stroke-[#8c8c8c]' }}"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path stroke-width="1.5" d="M12 .587l3.668 7.568L24 9.748l-6 5.848L19.335 24 12 19.897 4.665 24 6 15.596 0 9.748l8.332-1.593z"/>
                    </svg>
                @endfor
                <span class="ml-1 text-[15px] font-bold text-[#ffb000]">
                    {{ number_format($review->rating, 1) }}
                </span>
            </div>

            <span class="min-w-0 truncate text-[15px] font-bold text-black"
                  title="{{ $review->variant->title }}">
                {{ $review->variant->title }}
            </span>
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
    </header>

    {{-- Текстовые части отзыва выводятся только если автор их заполнил. --}}
    <div class="w-full pb-[2px] pt-[17px] text-[15px]">
        @php
            $reviewParts = collect([
                ['label' => 'Преимущества', 'text' => $review->advantages],
                ['label' => 'Недостатки', 'text' => $review->disadvantages],
                ['label' => 'Комментарий', 'text' => $review->comment],
            ])->filter(fn ($part) => filled($part['text']))->values();
        @endphp

        @foreach($reviewParts as $index => $text)
            <p class="{{ $index === $reviewParts->count() - 1 ? 'mb-[13px]' : 'mb-[8px]' }}">
                <strong>{{ $text['label'] }}:</strong>
                {{ $text['text'] }}
            </p>
        @endforeach

        @if($review->addition && $review->addition_is_published)
            <p class="mb-[13px]">
                <strong>Дополнено:</strong>
                {{ $review->addition }}
            </p>
        @endif
    </div>

    @if(auth()->id() === $review->user_id)
        {{-- Удалять свой отзыв может только его автор. --}}
        <div class="flex w-full items-center justify-between border-t border-dashed border-gray-300 pb-[25px] pt-[21px] text-left">
            @if($review->addition === null)
                <a href="{{ route('catalog.variant.reviews', $review->variant) }}"
                   data-review-form="addition"
                   data-review-id="{{ $review->id }}"
                   data-form-action="{{ route('reviews.addition', $review) }}"
                   data-product-title="{{ $review->variant->title }}"
                   data-product-image="{{ $review->variant->mainImage() }}"
                   class="text-[13px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                    Дополнить отзыв
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
        <div class="border-b border-dashed border-gray-300"></div>
        <section class="w-full pt-[4px] pl-5 text-left">
            <div class="mb-[5px] flex h-[38px] items-center gap-[7px] pl-1 pt-[7px]">
                <time class="text-[15px] text-[#8c8c8c]"
                      datetime="{{ $review->reply->created_at->toIso8601String() }}">
                    {{ $review->reply->created_at->locale('ru')->diffForHumans() }}
                </time>
                <span class="flex items-center">
                    @include('products.icons.my-shop')
                </span>
            </div>
            <div class="pb-[2px] pl-1">
                <p class="mb-[13px] text-[15px] text-black">
                    {{ $review->reply->body }}
                </p>
            </div>
        </section>
    @endif
</article>
