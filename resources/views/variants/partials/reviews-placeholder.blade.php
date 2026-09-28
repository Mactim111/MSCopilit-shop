@php
    $totalReviews = $reviewStats['count'];
    $visibleReviews = min($totalReviews, 5);
    $formattedRating = rtrim(rtrim(number_format($reviewStats['rating'], 2, '.', ''), '0'), '.');
    $isReviewRestricted = auth()->check() && !$canReview;
@endphp

<div class="mt-[10px] mb-[70px] w-[1110px] max-w-full text-left">
    <div class="w-[864px] max-w-full">
        @if($totalReviews === 0)
            <div class="pb-10">
                <h2 class="mb-5 text-2xl font-bold text-black">
                    У этого товара еще нет отзывов
                </h2>

                <p class="mb-5 text-[15px]">
                    Оставьте свой отзыв первым о
                    <span>{{ $variant->title }}</span>
                </p>

                @include('variants.partials.reviews-add-button', [
                    'isReviewRestricted' => $isReviewRestricted,
                    'buttonWidth' => 'w-[226px]',
                ])

                @if($isReviewRestricted)
                    <p class="mt-[15px] text-[15px] text-[#dc092e]">
                        Оставлять отзывы могут только пользователи, купившие этот товар
                    </p>
                @endif
            </div>
        @else
            {{-- Общая оценка и распределение рейтинга по опубликованным отзывам всех вариантов. --}}
            <div class="mb-8 flex h-[100px] w-full items-start gap-[5px] border border-[#8c8c8c] pb-1 pl-[65px] pr-14">
                <div class="w-[175px] shrink-0 pt-px text-[92px] font-bold leading-none text-[#FFB000]">
                    {{ $formattedRating }}
                </div>

                <div class="flex flex-1 flex-col pt-[14px]">
                    @for($stars = 5; $stars >= 1; $stars--)
                        @php
                            $ratingCount = $reviewStats['distribution'][$stars];
                            $barPercent = $totalReviews > 0
                                ? ($ratingCount / $totalReviews) * 100
                                : 0;
                        @endphp
                        <div class="mb-[3px] flex h-[12px] items-center gap-[2px] last:mb-0">
                            <div class="flex h-[10px] w-[66px] shrink-0 items-center gap-1">
                                @for($star = 0; $star < $stars; $star++)
                                    <svg class="h-[10px] w-[10px] shrink-0 fill-[#FFB000]"
                                         viewBox="0 0 24 24"
                                         aria-hidden="true">
                                        <path d="M12 .587l3.668 7.568L24 9.748l-6 5.848L19.335 24 12 19.897 4.665 24 6 15.596 0 9.748l8.332-1.593z"/>
                                    </svg>
                                @endfor
                            </div>

                            <div class="h-[2px] w-[431px] max-w-full bg-[#8c8c8c]">
                                <div class="h-full bg-[#FFB000]"
                                     style="width: {{ number_format($barPercent, 2, '.', '') }}%"></div>
                            </div>

                            <span class="pl-[1px] text-[12px] leading-none text-[#8c8c8c]">
                                {{ $ratingCount }}
                            </span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Список общий по умолчанию; второй режим фильтрует только текущий вариант. --}}
            <div class="mb-6 flex h-10 items-center gap-3"
                 data-review-scopes
                 data-variant-count="{{ $currentVariantReviewCount }}">
                <button type="button"
                        data-review-scope="all"
                        class="rounded-lg border border-red-600 bg-red-600 px-5 py-2 text-[15px] text-white">
                    Все отзывы
                </button>
                <button type="button"
                        data-review-scope="variant"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-[15px] text-gray-700 hover:border-red-600">
                    Этот вариант товара ({{ $currentVariantReviewCount }})
                </button>
            </div>

            {{-- Сортировка и кнопка отзыва; сортировка пока только визуальная. --}}
            <div class="flex h-10 items-center justify-between">
                <button type="button"
                        class="flex items-center rounded-lg border border-black px-[42px] py-2 text-[15px] text-black">
                    <svg class="mr-[18px] h-5 w-5 shrink-0"
                         viewBox="0 0 20 20"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.5"
                         aria-hidden="true">
                        <path d="M6 16V4m0 0L3.5 6.5M6 4l2.5 2.5M14 4v12m0 0l-2.5-2.5M14 16l2.5-2.5"/>
                    </svg>
                    По дате добавления
                </button>

                @include('variants.partials.reviews-add-button', [
                    'isReviewRestricted' => $isReviewRestricted,
                ])
            </div>
        @endif

        @if($totalReviews > 0 && $isReviewRestricted)
            <p class="mt-[15px] text-[15px] text-[#dc092e]">
                Оставлять отзывы могут только пользователи, купившие этот товар
            </p>
        @endif

        @if($totalReviews > 0)
            {{-- AJAX-контейнер получает первые пять карточек и следующие страницы по запросу. --}}
            <div class="w-full"
                 data-review-list
                 data-product-id="{{ $product->id }}"
                 data-variant-id="{{ $variant->id }}"
                 data-reviews-url="{{ route('reviews.index', $variant) }}"
                 data-total-reviews="{{ $totalReviews }}">
            </div>

            <div class="flex min-h-10 w-full items-start justify-between pt-[18px] text-[12px] text-[#8c8c8c]"
                 data-reviews-pagination
                 data-visible-count="{{ $visibleReviews }}"
                 data-total-count="{{ $totalReviews }}">
                @if($totalReviews > 5)
                    <button type="button"
                            data-reviews-load-more
                            class="text-left text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                        Показать ещё
                    </button>
                    <span class="ml-auto text-right" data-reviews-count-label>
                        Показано <span data-reviews-visible-count>{{ $visibleReviews }}</span>
                        из {{ $totalReviews }}
                    </span>
                @else
                    <span class="mr-auto text-left" data-reviews-count-label>
                        Показано {{ $visibleReviews }} из {{ $totalReviews }}
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>

@guest
    <div id="review-login-modal"
         class="fixed inset-0 z-[999] hidden items-center justify-center bg-[#231f20bf] px-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="review-login-modal-title">
        <div class="relative min-h-[380px] w-[500px] max-w-full rounded-lg bg-white px-[40px] py-8 sm:px-[50px]"
             data-review-modal-panel>
            <button type="button"
                    data-review-modal-close
                    aria-label="Закрыть окно"
                    class="absolute right-[10px] top-[10px] text-[28px] leading-[19px] text-[#231f20] hover:text-red-600">
                &times;
            </button>

            <div class="text-left">
                <h2 id="review-login-modal-title"
                    class="mb-6 text-[32px] font-bold leading-tight text-black">
                    Написать отзыв
                </h2>

                <p class="mb-4 mt-10 flex text-[20px] font-bold leading-tight text-black">
                    Данная рубрика доступна только для авторизованных пользователей
                </p>

                <a href="{{ route('login') }}"
                   class="mb-6 block w-full rounded-lg border border-[#dc092e] bg-[#dc092e] px-5 py-2 text-center text-[15px] font-bold text-white hover:bg-white hover:text-[#dc092e]">
                    Войти
                </a>

                <p class="mb-[5px] text-[12px] leading-tight text-[#8c8c8c]">
                    Спасибо, что решили поделиться опытом!<br>
                    Ваш отзыв будет опубликован через некоторое время после проверки модератором.<br>
                    Обращаем Ваше внимание, что мы оставляем за собой право не публиковать отзывы.
                </p>

                <a href="#"
                   class="text-[15px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                    Читать все правила
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('review-login-modal');
            const panel = modal?.querySelector('[data-review-modal-panel]');
            const openButtons = document.querySelectorAll('[data-review-action]');
            const closeButton = modal?.querySelector('[data-review-modal-close]');

            if (!modal || openButtons.length === 0) return;

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            openButtons.forEach(button => {
                button.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            closeButton?.addEventListener('click', closeModal);
            modal.addEventListener('click', event => {
                if (event.target === modal && !panel?.contains(event.target)) {
                    closeModal();
                }
            });
        });
    </script>
@endguest

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const list = document.querySelector('[data-review-list]');
        const scopes = document.querySelector('[data-review-scopes]');
        const pagination = document.querySelector('[data-reviews-pagination]');
        const loadMoreButton = pagination?.querySelector('[data-reviews-load-more]');
        const countLabel = pagination?.querySelector('[data-reviews-count-label]');
        const endpoint = list?.dataset.reviewsUrl;
        let scope = 'all';
        let loadedCount = 0;
        let totalCount = Number(list?.dataset.totalReviews || 0);
        let isLoading = false;

        const notifyError = message => {
            if (typeof window.showFlashMessage === 'function') {
                window.showFlashMessage(message, 'error');
            } else {
                console.error(message);
            }
        };

        const updatePagination = (total, loaded, hasMore) => {
            if (!pagination || !countLabel) return;

            totalCount = total;
            loadedCount = loaded;
            pagination.dataset.visibleCount = String(loaded);
            pagination.dataset.totalCount = String(total);
            countLabel.innerHTML = total > 5
                ? `Показано <span data-reviews-visible-count>${loaded}</span> из ${total}`
                : `Показано ${loaded} из ${total}`;
            countLabel.classList.toggle('ml-auto', hasMore);
            countLabel.classList.toggle('mr-auto', !hasMore);
            loadMoreButton?.classList.toggle('hidden', !hasMore);
        };

        const fetchReviews = async ({reset = false} = {}) => {
            if (!list || !endpoint || isLoading) return;
            isLoading = true;
            if (loadMoreButton) loadMoreButton.disabled = true;
            scopes?.querySelectorAll('[data-review-scope]').forEach(button => {
                button.disabled = true;
            });

            const offset = reset ? 0 : loadedCount;
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('scope', scope);
            url.searchParams.set('offset', String(offset));

            try {
                const response = await fetch(url, {
                    headers: {'Accept': 'application/json'},
                    credentials: 'same-origin',
                });
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || 'Не удалось загрузить отзывы.');
                }

                if (reset) {
                    list.innerHTML = payload.html;
                } else {
                    list.insertAdjacentHTML('beforeend', payload.html);
                }

                if (payload.total === 0) {
                    list.innerHTML = '<p class="py-6 text-[15px] text-[#8c8c8c]">Для выбранного варианта отзывов пока нет.</p>';
                }

                updatePagination(payload.total, payload.loaded, payload.has_more);
                scopes?.querySelectorAll('[data-review-scope]').forEach(button => {
                    const isActive = button.dataset.reviewScope === scope;
                    button.classList.toggle('border-red-600', isActive);
                    button.classList.toggle('bg-red-600', isActive);
                    button.classList.toggle('text-white', isActive);
                    button.classList.toggle('border-gray-300', !isActive);
                    button.classList.toggle('bg-white', !isActive);
                    button.classList.toggle('text-gray-700', !isActive);
                });
            } catch (error) {
                notifyError(error.message || 'Не удалось загрузить отзывы.');
            } finally {
                isLoading = false;
                if (loadMoreButton) loadMoreButton.disabled = false;
                scopes?.querySelectorAll('[data-review-scope]').forEach(button => {
                    button.disabled = false;
                });
            }
        };

        scopes?.querySelectorAll('[data-review-scope]').forEach(button => {
            button.addEventListener('click', () => {
                if (scope === button.dataset.reviewScope) return;
                scope = button.dataset.reviewScope;
                fetchReviews({reset: true});
            });
        });

        loadMoreButton?.addEventListener('click', () => fetchReviews());

        document.addEventListener('product-tab:changed', event => {
            if (event.detail?.tab === 'reviews' && loadedCount === 0 && totalCount > 0) {
                fetchReviews({reset: true});
            }
        });

        if (list && !document.querySelector('[data-product-tab-panel="reviews"]')?.classList.contains('hidden')) {
            fetchReviews({reset: true});
        }

        list?.addEventListener('click', async event => {
            const button = event.target.closest('[data-review-vote]');
            if (!button || button.disabled) return;

            button.disabled = true;
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({type: button.dataset.type}),
                });
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || 'Не удалось сохранить реакцию.');
                }

                const card = button.closest('[data-review-card]');
                card.querySelector('[data-vote-count="like"]').textContent = payload.like_count;
                card.querySelector('[data-vote-count="dislike"]').textContent = payload.dislike_count;
            } catch (error) {
                notifyError(error.message || 'Не удалось сохранить реакцию.');
            } finally {
                button.disabled = false;
            }
        });
    });
</script>
