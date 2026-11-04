@props(['review'])

@php
    $variant = $review->variant;
    $isDeleted = $review->trashed();
    $canManageChildren = ! $isDeleted && $review->is_published;
    $moderationStatuses = [
        \App\Models\Review::MODERATION_PENDING => ['label' => 'На модерации', 'classes' => 'border-[#2c9cdd] bg-[#2c9cdd]'],
        \App\Models\Review::MODERATION_APPROVED => ['label' => 'Одобрен', 'classes' => 'border-[#8bc34a] bg-[#8bc34a]'],
        \App\Models\Review::MODERATION_REJECTED => ['label' => 'Отклонён', 'classes' => 'border-[#dc032e] bg-[#dc032e]'],
    ];
    $reviewStatus = $moderationStatuses[$review->moderation_status] ?? $moderationStatuses[\App\Models\Review::MODERATION_PENDING];
    $additionStatus = $moderationStatuses[$review->addition_moderation_status] ?? $moderationStatuses[\App\Models\Review::MODERATION_PENDING];
    $authorName = $review->user?->name ?? 'Покупатель удалён';
    $disabledActionClass = 'cursor-not-allowed text-gray-400';
    $activeActionClass = 'cursor-pointer text-[#007eff] hover:text-[#0064cc]';
    $activeDeleteClass = 'cursor-pointer text-[#dc092e] hover:text-[#a80723]';
@endphp

<article class="w-full rounded-[10px] border border-[#e5e5e5] bg-white px-5 py-6 md:px-8 {{ $isDeleted ? 'opacity-75' : '' }}">
    <header class="mb-5 flex flex-wrap items-start justify-between gap-4 border-b border-dashed border-[#e5e5e5] pb-4">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#ededed] text-[16px] font-semibold text-[#555]">
                {{ mb_strtoupper(mb_substr($authorName, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="max-w-full truncate text-[15px] font-semibold text-[#231f20]">{{ $authorName }}</span>
                    <span class="inline-flex min-h-[20px] items-center rounded-[4px] border px-[7px] py-[2px] text-[12px] leading-none text-white {{ $isDeleted ? 'border-gray-400 bg-gray-400' : $reviewStatus['classes'] }}">
                        {{ $isDeleted ? 'Удалён' : $reviewStatus['label'] }}
                    </span>
                </div>
                <time class="text-[13px] text-[#8c8c8c]" datetime="{{ $review->created_at->toIso8601String() }}">
                    Добавлен {{ $review->created_at->format('d.m.Y') }}
                </time>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-3 text-[#8c8c8c]" aria-label="Оценки полезности отзыва">
            <span class="flex items-center gap-1" title="Полезно">
                <span class="text-[14px]">{{ $review->likes_count }}</span>
                <span class="flex h-5 w-5 items-center justify-center">@include('products.icons.r-likes__like')</span>
            </span>
            <span class="flex items-center gap-1" title="Не полезно">
                <span class="text-[14px]">{{ $review->dislikes_count }}</span>
                <span class="flex h-5 w-5 items-center justify-center">@include('products.icons.r-likes__dislike')</span>
            </span>
        </div>
    </header>

    <div class="mb-5 flex flex-wrap items-center gap-2 text-[20px] font-semibold">
        @if($review->order)
            <a href="{{ route('admin.orders.show', $review->order) }}"
               class="text-[#007eff] hover:text-[#0064cc]">
                Заказ №{{ $review->order->id }} от {{ $review->order->created_at->format('d.m.Y') }}
            </a>
        @else
            <span class="text-gray-500">Заказ не привязан</span>
        @endif
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-left">
        <div class="flex items-center gap-[2px]" aria-label="Оценка автора: {{ $review->rating }} из 5">
            @for($star = 1; $star <= 5; $star++)
                <svg class="h-4 w-4 {{ $star <= $review->rating ? 'fill-[#ffb000]' : 'fill-white stroke-[#8c8c8c]' }}"
                     viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-width="1.5" d="M12 .587l3.668 7.568L24 9.748l-6 5.848L19.335 24 12 19.897 4.665 24 6 15.596 0 9.748l8.332-1.593z"/>
                </svg>
            @endfor
            <span class="ml-1 text-[15px] font-semibold text-[#ffb000]">{{ number_format($review->rating, 1) }}</span>
        </div>
        @if($variant)
            <a href="{{ route('catalog.variant', $variant) }}" class="text-[15px] font-semibold text-[#231f20] hover:text-[#007eff]">
                {{ $variant->title }}
            </a>
        @else
            <span class="text-[15px] font-semibold text-gray-500">Вариант товара недоступен</span>
        @endif
    </div>

    <div class="mb-5 text-left text-[15px] text-black">
        <p class="mb-2"><strong>Преимущества:</strong> {{ $review->advantages }}</p>
        <p class="mb-2"><strong>Недостатки:</strong> {{ $review->disadvantages }}</p>
        <p><strong>Комментарий:</strong> {{ $review->comment }}</p>
    </div>

    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 pb-4 text-[13px]">
        @if($isDeleted)
            <form method="POST" action="{{ route('admin.reviews.restore', $review->id) }}"
                  onsubmit="return confirm('Вернуть отзыв на модерацию?')">
                @csrf
                @method('PATCH')
                <button class="{{ $activeActionClass }}">На модерацию</button>
            </form>
            <form method="POST" action="{{ route('admin.reviews.force-delete', $review->id) }}"
                  onsubmit="return confirm('Удалить отзыв окончательно вместе со всеми файлами? Это действие нельзя отменить.')">
                @csrf
                @method('DELETE')
                <button class="{{ $activeDeleteClass }}">Удалить окончательно</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.reviews.moderation.update', $review) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $review->is_published ? \App\Models\Review::MODERATION_PENDING : \App\Models\Review::MODERATION_APPROVED }}">
                <button class="{{ $activeActionClass }}">
                    {{ $review->is_published ? 'На модерацию' : 'Опубликовать' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                  onsubmit="return confirm('Удалить отзыв вместе с дополнением и ответом магазина?')">
                @csrf
                @method('DELETE')
                <button class="{{ $activeDeleteClass }}">Удалить</button>
            </form>
            <button type="button" class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}"
                    data-admin-reply-trigger
                    data-form-action="{{ route($review->reply && ! $review->reply->trashed() ? 'admin.reviews.reply.update' : 'admin.reviews.reply.store', $review) }}"
                    data-form-method="{{ $review->reply && ! $review->reply->trashed() ? 'PUT' : 'POST' }}"
                    data-reply-body="{{ $review->reply && ! $review->reply->trashed() ? $review->reply->body : '' }}"
                    @disabled(! $canManageChildren)>
                Ответить на отзыв
            </button>
        @endif
    </div>

    @if($review->images->isNotEmpty())
        <section class="mb-5 border-t border-dashed border-[#e5e5e5] pt-4">
            <h3 class="mb-3 text-[14px] font-semibold text-[#231f20]">Фото и видео</h3>
            <div class="flex flex-wrap gap-4">
                @foreach($review->images as $image)
                    @php($imageDeleted = $isDeleted || $image->trashed())
                    <div class="flex w-[88px] flex-col items-center gap-2 text-center {{ $imageDeleted ? 'text-gray-400' : 'text-[#231f20]' }}">
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                           target="_blank" rel="noopener"
                           class="block h-[80px] w-[80px] overflow-hidden rounded-lg bg-gray-100 {{ $imageDeleted ? 'pointer-events-none grayscale opacity-50' : '' }}">
                            @if($image->media_type === 'video')
                                <video class="h-full w-full object-cover" preload="metadata" aria-label="Видео к отзыву">
                                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}">
                                </video>
                            @else
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                                     alt="Фотография к отзыву" class="h-full w-full object-cover">
                            @endif
                        </a>
                        @if(! $isDeleted && $image->trashed())
                            <form method="POST" action="{{ route('admin.reviews.images.restore', [$review, $image->id]) }}"
                                  onsubmit="return confirm('Восстановить {{ $image->media_type === 'video' ? 'видео' : 'фото' }} к отзыву?')">
                                @csrf
                                @method('PATCH')
                                <button class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}" @disabled(! $canManageChildren)>
                                    Восстановить
                                </button>
                            </form>
                        @elseif(! $image->trashed() && ! $isDeleted)
                            <form method="POST" action="{{ route('admin.reviews.images.destroy', [$review, $image]) }}"
                                  onsubmit="return confirm('Удалить {{ $image->media_type === 'video' ? 'видео' : 'фото' }} к отзыву?')">
                                @csrf
                                @method('DELETE')
                                <button class="{{ $canManageChildren ? $activeDeleteClass : $disabledActionClass }} text-[13px]" @disabled(! $canManageChildren)>Удалить</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($review->addition !== null)
        @php($additionDeleted = $review->addition_deleted_at !== null)
        <section class="border-t border-dashed border-[#e5e5e5] pl-6 py-4 {{ $additionDeleted || $isDeleted ? 'text-gray-400' : 'text-[#231f20]' }}">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <h3 class="text-[15px] text-[#8c8c8c]">Дополнен {{ $review->addition_updated_at->format('d.m.Y') }}</h3>
                <span class="inline-flex min-h-[20px] items-center rounded-[4px] border px-[7px] py-[2px] text-[12px] leading-none text-white {{ $additionDeleted || $isDeleted ? 'border-gray-400 bg-gray-400' : $additionStatus['classes'] }}">
                    {{ $additionDeleted || $isDeleted ? 'Удалено' : $additionStatus['label'] }}
                </span>
            </div>
            <p class="whitespace-pre-line text-[15px]">{{ $review->addition }}</p>
            @if(! $isDeleted)
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-[13px]">
                    @if($additionDeleted)
                        <form method="POST" action="{{ route('admin.reviews.addition.restore', $review) }}"
                              onsubmit="return confirm('Восстановить дополнение к отзыву?')">
                            @csrf
                            @method('PATCH')
                            <button class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}" @disabled(! $canManageChildren)>Восстановить</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.reviews.addition-moderation.update', $review) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $review->addition_is_published ? \App\Models\Review::MODERATION_PENDING : \App\Models\Review::MODERATION_APPROVED }}">
                            <button class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}" @disabled(! $canManageChildren)>
                                {{ $review->addition_is_published ? 'На модерацию' : 'Опубликовать' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.reviews.addition.destroy', $review) }}"
                              onsubmit="return confirm('Удалить дополнение к отзыву?')">
                            @csrf
                            @method('DELETE')
                            <button class="{{ $canManageChildren ? $activeDeleteClass : $disabledActionClass }}" @disabled(! $canManageChildren)>Удалить</button>
                        </form>
                    @endif
                    <button type="button" class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}"
                            data-admin-reply-trigger
                            data-form-action="{{ route($review->reply && ! $review->reply->trashed() ? 'admin.reviews.reply.update' : 'admin.reviews.reply.store', $review) }}"
                            data-form-method="{{ $review->reply && ! $review->reply->trashed() ? 'PUT' : 'POST' }}"
                            data-reply-body="{{ $review->reply && ! $review->reply->trashed() ? $review->reply->body : '' }}"
                            @disabled(! $canManageChildren)>Ответить на отзыв</button>
                </div>
            @endif
        </section>
    @endif

    @if($review->reply)
        @php($replyDeleted = $review->reply->trashed())
        <section class="mb-5 pt-4 pl-6 border-t border-dashed border-[#e5e5e5] {{ $replyDeleted || $isDeleted ? 'text-gray-400' : 'text-[#231f20]' }}">
            <div class="mb-[5px] flex h-[38px] items-center pl-1" aria-label="Ответ магазина">
                <span class="flex items-center {{ $replyDeleted || $isDeleted ? 'opacity-40' : '' }}">
                    @include('products.icons.my-shop')
                </span>
                <time class="text-[15px] {{ $replyDeleted || $isDeleted ? 'text-gray-400' : 'text-[#8c8c8c]' }}"
                      datetime="{{ $review->reply->created_at->toIso8601String() }}">
                    {{ $review->reply->created_at->format('d.m.Y') }}
                </time>
                @if($replyDeleted || $isDeleted)
                    <span class="ml-2 pt-[7px] text-[12px]">Удалён</span>
                @endif
            </div>
            <p class="whitespace-pre-line text-[15px] {{ $replyDeleted || $isDeleted ? 'text-gray-400' : 'text-black' }}">{{ $review->reply->body }}</p>
            @if(! $isDeleted)
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-[13px]">
                    @if($replyDeleted)
                        <form method="POST" action="{{ route('admin.reviews.reply.restore', $review) }}"
                              onsubmit="return confirm('Восстановить ответ на отзыв?')">
                            @csrf
                            @method('PATCH')
                            <button class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}" @disabled(! $canManageChildren)>Восстановить</button>
                        </form>
                    @else
                        <button type="button" class="{{ $canManageChildren ? $activeActionClass : $disabledActionClass }}"
                                data-admin-reply-trigger
                                data-form-action="{{ route('admin.reviews.reply.update', $review) }}"
                                data-form-method="PUT"
                                data-reply-body="{{ $review->reply->body }}"
                                @disabled(! $canManageChildren)>Редактировать</button>
                        <form method="POST" action="{{ route('admin.reviews.reply.destroy', $review) }}"
                              onsubmit="return confirm('Удалить ответ на отзыв?')">
                            @csrf
                            @method('DELETE')
                            <button class="{{ $canManageChildren ? $activeDeleteClass : $disabledActionClass }}" @disabled(! $canManageChildren)>Удалить</button>
                        </form>
                    @endif
                </div>
            @endif
        </section>
    @endif
</article>
