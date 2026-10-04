@extends('layouts.main')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8">

        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Заказ №{{ $order->id }}</h1>
            <p class="text-gray-600 mt-1">Оформлен: {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <!-- Карточка статуса -->
        <div class="bg-white shadow rounded-xl p-6 mb-8 border border-gray-100">
            <h2 class="text-xl font-semibold mb-4">Статус заказа</h2>

            <div class="flex items-center gap-3">
            <span class="px-4 py-2 rounded-full text-sm font-medium
                @if($order->status === 'new') bg-blue-100 text-blue-700
                @elseif($order->status === 'paid') bg-green-100 text-green-700
                @elseif($order->status === 'canceled') bg-red-100 text-red-700
                @else bg-gray-100 text-gray-700 @endif">
                {{ ucfirst($order->status) }}
            </span>
            </div>
        </div>

        <!-- Товары -->
        <div class="bg-white shadow rounded-xl p-6 border border-gray-100">
            <h2 class="text-xl font-semibold mb-6">Товары в заказе</h2>

            <div class="space-y-6">
                @foreach($order->items as $item)
                    @php
                        $variant = $item->variant;
                        $review = $variant
                            ? $reviewsByVariant->get($variant->id)
                            : null;
                        $canReviewOrderItem = in_array($order->status, ['paid', 'shipped'], true)
                            && $variant !== null;
                    @endphp
                    <div class="flex items-center gap-6 pb-6 border-b last:border-b-0">

                        <!-- Фото товара -->
                        <div class="w-24 h-24 flex-shrink-0">
                            @if($variant)
                                <a href="{{ route('catalog.variant', $variant->slug) }}">
                                    <img src="{{ $variant->mainImage() }}"
                                        alt="{{ $variant->title }}"
                                        class="w-full h-full object-cover rounded-lg shadow-sm">
                                </a>
                            @endif
                        </div>

                        <!-- Информация -->
                        <div class="flex-1">
                            @if($variant)
                                <a href="{{ route('catalog.variant', $variant->slug) }}" class="text-lg font-semibold text-gray-900">
                                    {{ $variant->title }}
                                </a>
                            @else
                                <p class="text-lg font-semibold text-gray-900">{{ $item->title }}</p>
                            @endif

                            <p class="text-gray-600 mt-1">
                                Количество: <span class="font-medium">{{ $item->quantity }}</span>
                            </p>

                            <p class="text-gray-600">
                                Цена за шт.:
                                <span class="font-medium text-xl">
                                    @if($variant)
                                        {!! $variant->formattedPrice(16, 16) !!}
                                    @else
                                        {{ number_format($item->price, 2, '.', ' ') }} BYN
                                    @endif
                                </span>
                            </p>

                            @if($canReviewOrderItem)
                                @if($review)
                                    <div class="mt-1 flex items-center justify-between">
                                        @if($review->addition === null)
                                            <a href="{{ route('catalog.variant.reviews', $variant) }}"
                                               data-review-form="addition"
                                               data-review-id="{{ $review->id }}"
                                               data-form-action="{{ route('reviews.addition', $review) }}"
                                               data-product-title="{{ $variant->title }}"
                                               data-product-image="{{ $variant->mainImage() }}"
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
                                                Удалить отзыв
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('catalog.variant.reviews', $variant) }}"
                                       data-review-form="create"
                                       data-form-action="{{ route('reviews.store', $variant) }}"
                                       data-product-title="{{ $variant->title }}"
                                       data-product-image="{{ $variant->mainImage() }}"
                                       class="text-[13px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                                        Добавить отзыв
                                    </a>
                                @endif
                            @endif
                        </div>

                        <!-- Сумма -->
                        <div class="text-right">
                            <p class="text-xl font-bold text-gray-900">
                                {!! $item->formattedSubtotal(16, 16) !!}
                            </p>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- Итог -->
        <div class="bg-white shadow rounded-xl p-6 mt-8 border border-gray-100">
            <h2 class="text-xl font-semibold mb-4">Итог заказа</h2>

            <div class="flex justify-between text-lg font-medium text-gray-900">
                <span>Сумма заказа:</span>
                <p class="text-xl font-bold text-gray-900">
                    {!! $item->formattedSubtotal(16, 16) !!}
                </p>
            </div>
        </div>

        <!-- Кнопка назад -->
        <div class="mt-8">
            <a href="{{ route('profile.orders') }}"
               class="inline-block px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition">
                ← Вернуться к списку заказов
            </a>
        </div>

    </div>

    @include('variants.partials.review-create-modal')
    @include('variants.partials.review-addition-modal')
    @include('variants.partials.review-action-result-modal')

@endsection
