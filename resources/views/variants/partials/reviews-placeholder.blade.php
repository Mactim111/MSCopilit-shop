<div class="pt-10 mb-[70px] text-left">
    <h2 class="mb-5 text-2xl font-bold text-black">
        У этого товара еще нет отзывов
    </h2>

    <p class="mb-5 text-[15px]">
        Оставьте свой отзыв первым о
        <span class="text-[15px]">{{ $variant->title }}</span>
    </p>

    @if(auth()->check() && !$canReview)
        <button type="button"
                disabled
                class="block h-[40px] w-[226px] cursor-not-allowed rounded-lg border border-[#e0e0e0] bg-[#e0e0e0] px-5 text-[15px] font-semibold text-white">
            Добавить отзыв
        </button>
        <p class="mt-[15px] text-[15px] text-[#dc092e]">
            Оставлять отзывы могут только пользователи, купившие этот товар
        </p>
    @else
        <button type="button"
                data-review-action
                class="block h-[40px] w-[226px] rounded-lg border border-red-600 bg-white px-5 text-[15px] font-semibold text-red-600 hover:bg-red-600 hover:text-white">
            Добавить отзыв
        </button>
    @endif
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

                <p class="text-[20px] font-bold leading-tight text-black">
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
                   class="text-[15px] text-[#007eff] hover:text-[#0064cc] transition-all duration-200">
                    Читать все правила
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('review-login-modal');
            const panel = modal?.querySelector('[data-review-modal-panel]');
            const openButton = document.querySelector('[data-review-action]');
            const closeButton = modal?.querySelector('[data-review-modal-close]');

            if (!modal || !openButton) return;

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            openButton.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });

            closeButton?.addEventListener('click', closeModal);
            modal.addEventListener('click', event => {
                if (event.target === modal && event.target !== panel) {
                    closeModal();
                }
            });
        });
    </script>
@endguest
