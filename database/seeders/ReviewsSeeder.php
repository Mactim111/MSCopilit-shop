<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Models\ReviewVote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ReviewsSeeder extends Seeder
{
    private const CUSTOMER_EMAILS = [
        'ivan@mail.ru',
        'peter@mail.ru',
        'sidor@mail.ru',
        'gleb@mail.ru',
        'igor@mail.ru',
        'panas@mail.ru',
        'maria@mail.ru',
    ];

    public function run(): void
    {
        $customers = User::query()
            ->whereIn('email', self::CUSTOMER_EMAILS)
            ->get()
            ->keyBy('email');
        $admin = User::query()->where('email', 'admin@mail.ru')->first();

        if ($customers->count() !== count(self::CUSTOMER_EMAILS) || ! $admin) {
            throw new RuntimeException('Reviews require the seeded customers and admin account.');
        }

        $variants = ProductVariant::query()
            ->whereIn('id', [1, 2])
            ->with(['images' => fn ($query) => $query
                ->where('is_active', true)
                ->whereBetween('position', [2, 4])
                ->orderBy('position')])
            ->get()
            ->keyBy('id');

        if ($variants->count() !== 2) {
            throw new RuntimeException('Reviews require product variants with IDs 1 and 2.');
        }

        foreach ($variants as $variant) {
            if ($variant->images->count() < 3) {
                throw new RuntimeException(
                    "Variant {$variant->id} must have gallery images at positions 2, 3, and 4."
                );
            }
        }

        $reviewData = $this->reviewData();

        DB::transaction(function () use ($customers, $admin, $variants, $reviewData): void {
            foreach ($reviewData as $index => $data) {
                $author = $customers->get($data['author']);
                $variant = $variants->get($data['variant_id']);

                if (! $author || ! $variant) {
                    throw new RuntimeException('A demo review refers to a missing customer or variant.');
                }

                $hasPaidOrder = $author->orders()
                    ->where('status', Order::STATUS_COMPLETED)
                    ->whereHas('items', fn ($query) => $query
                        ->where('product_variant_id', $variant->id))
                    ->exists();

                if (! $hasPaidOrder) {
                    throw new RuntimeException(
                        "Customer {$author->email} must have a completed order for variant {$variant->id}."
                    );
                }

                $createdAt = now()->subMonths($data['months_ago']);
                $review = new Review([
                    'user_id' => $author->id,
                    'product_variant_id' => $variant->id,
                    'moderation_status' => $data['status'],
                    'rating' => $data['rating'],
                    'advantages' => $data['advantages'],
                    'disadvantages' => $data['disadvantages'],
                    'comment' => $data['comment'],
                    'addition' => $data['addition'],
                    'addition_updated_at' => $data['addition_months_ago'] !== null
                        ? now()->subMonths($data['addition_months_ago'])->subDays($data['addition_days_ago'])
                        : null,
                    'addition_moderation_status' => $data['addition'] !== null
                        ? ($data['addition_status'] ?? Review::MODERATION_APPROVED)
                        : null,
                    'addition_is_published' => $data['addition'] !== null
                        && ($data['addition_status'] ?? Review::MODERATION_APPROVED) === Review::MODERATION_APPROVED,
                ]);
                $review->created_at = $createdAt;
                $review->updated_at = $createdAt;
                $review->save();

                if ($data['photo_count'] > 0) {
                    $galleryImages = $variant->images->shuffle()->take($data['photo_count']);

                    foreach ($galleryImages as $position => $galleryImage) {
                        $path = Str::startsWith($galleryImage->path, 'storage/')
                            ? Str::after($galleryImage->path, 'storage/')
                            : $galleryImage->path;

                        if (! Storage::disk('public')->exists($path)) {
                            throw new RuntimeException("Demo review image does not exist: {$path}");
                        }

                        ReviewImage::create([
                            'review_id' => $review->id,
                            'path' => $path,
                            'media_type' => 'image',
                            'position' => $position + 1,
                        ]);
                    }
                }

                if ($data['reply'] !== null) {
                    $replyAt = now()->subMonths($data['reply_months_ago'])
                        ->subDays($data['reply_days_ago']);
                    $reply = new ReviewReply([
                        'admin_id' => $admin->id,
                        'body' => $data['reply'],
                    ]);
                    $reply->created_at = $replyAt;
                    $reply->updated_at = $replyAt;
                    $review->reply()->save($reply);
                }

                $voters = $customers
                    ->reject(fn (User $voter) => $voter->id === $author->id)
                    ->shuffle()
                    ->take(2 + ($index % 4));

                foreach ($voters as $voter) {
                    ReviewVote::create([
                        'review_id' => $review->id,
                        'user_id' => $voter->id,
                        'type' => random_int(1, 4) === 1
                            ? ReviewVote::TYPE_DISLIKE
                            : ReviewVote::TYPE_LIKE,
                    ]);
                }
            }
        });

        $this->command?->info('Created 14 demo reviews with gallery images, replies, and votes.');
    }

    private function reviewData(): array
    {
        return [
            [
                'variant_id' => 1, 'author' => 'ivan@mail.ru', 'rating' => 5,
                'advantages' => 'Чёткий экран, приятный звук и удобный размер.',
                'disadvantages' => 'Для переноса файлов на внешний накопитель нужен переходник.',
                'comment' => 'Телефон работает быстро, корпус выглядит аккуратно. Для повседневных задач всего хватает.',
                'months_ago' => 18, 'status' => Review::MODERATION_APPROVED,
                'addition' => 'Пользуюсь уже почти год: сбоев и проблем не появилось.',
                'addition_status' => Review::MODERATION_APPROVED,
                'addition_months_ago' => 9, 'addition_days_ago' => 0,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 2,
            ],
            [
                'variant_id' => 1, 'author' => 'peter@mail.ru', 'rating' => 5,
                'advantages' => 'Компактный, быстрый, приятно лежит в руке.',
                'disadvantages' => 'Звонок хотелось бы сделать громче.',
                'comment' => 'Это мой первый телефон этой марки. Освоился быстрее, чем ожидал, приложения открываются без задержек.',
                'months_ago' => 24, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => 'Спасибо, что поделились впечатлениями. Желаем приятного пользования!',
                'reply_months_ago' => 7, 'reply_days_ago' => 0,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 1, 'author' => 'sidor@mail.ru', 'rating' => 5,
                'advantages' => 'Камера, плавная работа интерфейса и качественный экран.',
                'disadvantages' => 'Стоимость выше, чем у многих альтернатив.',
                'comment' => 'Перешла на эту модель после другого смартфона. Особенно понравились камера и то, как быстро привыкла к системе.',
                'months_ago' => 20, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 3,
            ],
            [
                'variant_id' => 1, 'author' => 'gleb@mail.ru', 'rating' => 4,
                'advantages' => 'Покупкой довольны, всё нужное работает.',
                'disadvantages' => 'Цена без скидки кажется высокой.',
                'comment' => 'Брал телефон в подарок. Получатель пользуется каждый день и пока не жалуется.',
                'months_ago' => 12, 'status' => Review::MODERATION_APPROVED,
                'addition' => 'За время использования всё работает исправно.',
                'addition_status' => Review::MODERATION_PENDING,
                'addition_months_ago' => 0, 'addition_days_ago' => 20,
                'reply' => 'Благодарим за отзыв и выбор нашего магазина!',
                'reply_months_ago' => 7, 'reply_days_ago' => 0,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 1, 'author' => 'igor@mail.ru', 'rating' => 5,
                'advantages' => 'Высокая скорость, яркий экран, хорошая основная камера.',
                'disadvantages' => 'Встроенной памяти может не хватить для большой медиатеки.',
                'comment' => 'Игры и повседневные приложения работают уверенно. Покупкой доволен.',
                'months_ago' => 8, 'status' => Review::MODERATION_REJECTED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 2,
            ],
            [
                'variant_id' => 1, 'author' => 'panas@mail.ru', 'rating' => 3,
                'advantages' => 'Устройство удобно настраивать, доставка была быстрой.',
                'disadvantages' => 'В моём экземпляре со временем стал хуже работать динамик.',
                'comment' => 'Сначала всё устраивало, но спустя несколько недель заметил перебои со звуком.',
                'months_ago' => 11, 'status' => Review::MODERATION_APPROVED,
                'addition' => 'После обращения в сервис проблему удалось диагностировать.',
                'addition_status' => Review::MODERATION_REJECTED,
                'addition_months_ago' => 2, 'addition_days_ago' => 0,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 1, 'author' => 'maria@mail.ru', 'rating' => 1,
                'advantages' => 'Внешний вид и стоимость.',
                'disadvantages' => 'Экран начал мерцать, затем появились полосы.',
                'comment' => 'С таким дефектом пользоваться устройством неудобно. Перед покупкой советую проверить гарантийные условия.',
                'months_ago' => 2, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => 'Сожалеем, что возникла такая ситуация. Рекомендуем обратиться в авторизованный сервисный центр для проверки устройства по гарантии.',
                'reply_months_ago' => 1, 'reply_days_ago' => 0,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 2, 'author' => 'ivan@mail.ru', 'rating' => 5,
                'advantages' => 'Красивый цвет, яркий экран и быстрая работа.',
                'disadvantages' => 'Нет.',
                'comment' => 'За свою цену отличный вариант. Пользуюсь несколько месяцев, неисправностей не было.',
                'months_ago' => 12, 'status' => Review::MODERATION_APPROVED,
                'addition' => 'После полугода использования впечатление осталось таким же хорошим.',
                'addition_status' => Review::MODERATION_APPROVED,
                'addition_months_ago' => 6, 'addition_days_ago' => 0,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 2,
            ],
            [
                'variant_id' => 2, 'author' => 'peter@mail.ru', 'rating' => 4,
                'advantages' => 'Удобный корпус, приятный экран, много полезных функций.',
                'disadvantages' => 'Автономность и камера средние.',
                'comment' => 'В целом модель понравилась, хотя ожидал немного большего от батареи.',
                'months_ago' => 18, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => 'Благодарим за обратную связь. Будем рады видеть вас снова!',
                'reply_months_ago' => 11, 'reply_days_ago' => 0,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 2, 'author' => 'sidor@mail.ru', 'rating' => 2,
                'advantages' => 'Удобный размер и аккуратный дизайн.',
                'disadvantages' => 'Заряда хватает не всегда на полный день, снимки в помещении получаются не лучшими.',
                'comment' => 'Возможно, мне попался неудачный экземпляр. Пока выбираю, куда обратиться на диагностику.',
                'months_ago' => 16, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => 'Понимаем ваше недовольство. Для проверки устройства можно записаться в сервисный центр.',
                'reply_months_ago' => 11, 'reply_days_ago' => 0,
                'photo_count' => 3,
            ],
            [
                'variant_id' => 2, 'author' => 'gleb@mail.ru', 'rating' => 5,
                'advantages' => 'Упаковка надёжная, устройство оригинальное, доставили быстро.',
                'disadvantages' => 'Пока не обнаружил.',
                'comment' => 'Покупкой доволен. Телефон соответствует описанию и хорошо справляется с повседневными задачами.',
                'months_ago' => 10, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 2, 'author' => 'igor@mail.ru', 'rating' => 4,
                'advantages' => 'Корпус выглядит отлично, зарядка быстрая.',
                'disadvantages' => 'До заявленного срока работы без подзарядки не дотягивает.',
                'comment' => 'При активном использовании заряжаю телефон ежедневно. В остальном всё устраивает.',
                'months_ago' => 20, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => 'Спасибо за отзыв. Передадим ваши замечания команде магазина.',
                'reply_months_ago' => 12, 'reply_days_ago' => 0,
                'photo_count' => 0,
            ],
            [
                'variant_id' => 2, 'author' => 'panas@mail.ru', 'rating' => 5,
                'advantages' => 'Стильный корпус, хорошая скорость и качественные фотографии.',
                'disadvantages' => 'Не нашёл.',
                'comment' => 'Выбирала между несколькими цветами и остановилась на этом. Телефон полностью оправдал ожидания.',
                'months_ago' => 4, 'status' => Review::MODERATION_PENDING,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => null, 'reply_months_ago' => null, 'reply_days_ago' => null,
                'photo_count' => 2,
            ],
            [
                'variant_id' => 2, 'author' => 'maria@mail.ru', 'rating' => 4,
                'advantages' => 'Хорошее качество снимков и приятный дизайн.',
                'disadvantages' => 'В комплекте не оказалось защитных аксессуаров.',
                'comment' => 'Покупали телефон в подарок. Он понравился, но чехол пришлось подобрать отдельно.',
                'months_ago' => 15, 'status' => Review::MODERATION_APPROVED,
                'addition' => null, 'addition_months_ago' => null, 'addition_days_ago' => null,
                'reply' => 'Спасибо за замечание. Проверим, чтобы описание комплектации было максимально точным.',
                'reply_months_ago' => 12, 'reply_days_ago' => 0,
                'photo_count' => 0,
            ],
        ];
    }
}
