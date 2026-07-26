<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // // Акции (ссылка, не группа категорий)
            // [
            //     'title' => 'Акции',
            //     'slug' => 'actions',
            //     'image' => 'storage/assets/img/actions.png',
            //     'parent_id' => null,
            // ],

            // ГРУППА КАТЕГОРИЙ: Смартфоны и гаджеты (id = 1 в табл. 'categories')
            [
                'title' => 'Смартфоны и гаджеты',
                'slug' => 'smartfony-i-gadzety',
                'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-i-gadzety.jpg',
                'parent_id' => null,
            ],


            // КАТЕГОРИЯ: Смартфоны, телефоны (id = 2 в табл. 'categories')
            [
                'title' => 'Смартфоны, телефоны',
                'slug' => 'smartfony-telefony',
                'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/smartfony-telefony.jpg',
                'parent_id' => 1,
                'brand_tiles_enabled' => true,
            ],

            //ПОДКАТЕГОРИИ:

            //(id = 3 в табл. 'categories')
            // [
            //     'title' => 'Смартфоны Apple iPhone',
            //     'slug' => 'smartfony-apple-iphone',
            //     'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/smartfony-apple-iphone.jpg',
            //     'parent_id' => 2,
            // ],
            //(id = 4 в табл. 'categories')
            // [
            //     'title' => 'Смартфоны Samsung',
            //     'slug' => 'smartfony-samsung',
            //     'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/smartfony-samsung.jpg',
            //     'parent_id' => 2,
            // ],
            //(id = 5 в табл. 'categories')
            // [
            //     'title' => 'Смартфоны Xiaomi',
            //     'slug' => 'smartfony-xiaomi',
            //     'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/smartfony-xiaomi.jpg',
            //     'parent_id' => 2,
            // ],

            //(id = 3 в табл. 'categories')
            [
                'title' => 'Смартфоны',
                'slug' => 'smartfony',
                'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/smartfony.jpg',
                'parent_id' => 2,
            ],

            // Ниже Пустые подкатегории для наполненности
            //(id = 4 в табл. 'categories')
            ['title' => 'Сетевые зарядные устройства', 'slug' => 'setevye-zaryadnye-ustroistva', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/setevye-zaryadnye-ustroistva.jpg', 'parent_id' => 2],
            //(id = 5 в табл. 'categories')
            ['title' => 'Кабели для смартфонов', 'slug' => 'kabeli-dlya-smartfonov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/kabeli-dlya-smartfonov.jpg', 'parent_id' => 2],
            //(id = 6 в табл. 'categories')
            ['title' => 'Портативные зарядные устройства', 'slug' => 'portativnye-zaryadnye-ustroistva', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/portativnye-zaryadnye-ustroistva.jpg', 'parent_id' => 2],
            //(id = 7 в табл. 'categories')
            ['title' => 'Сотовые телефоны', 'slug' => 'sotovye-telefony', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/sotovye-telefony.jpg', 'parent_id' => 2],
            //(id = 8 в табл. 'categories')
            ['title' => 'Карты памяти', 'slug' => 'karty-pamyati', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/karty-pamyati.jpg', 'parent_id' => 2],
            //(id = 9 в табл. 'categories')
            ['title' => 'Чехлы для смартфонов', 'slug' => 'cexly-dlya-smartfonov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/cexly-dlya-smartfonov.jpg', 'parent_id' => 2],
            //(id = 10 в табл. 'categories')
            ['title' => 'Радиотелефоны DECT', 'slug' => 'radiotelefony-dect', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/radiotelefony-dect.jpg', 'parent_id' => 2],
            //(id = 11 в табл. 'categories')
            ['title' => 'Моноподы для селфи', 'slug' => 'monopody-dlya-selfi', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/monopody-dlya-selfi.jpg', 'parent_id' => 2],
            //(id = 12 в табл. 'categories')
            ['title' => 'Проводные телефоны', 'slug' => 'provodnye-telefony', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/provodnye-telefony.jpg', 'parent_id' => 2],
            //(id = 13 в табл. 'categories')
            ['title' => 'Сим-карты', 'slug' => 'sim-karty', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/sim-karty.jpg', 'parent_id' => 2],
            //(id = 14 в табл. 'categories')
            ['title' => 'Защитные стекла', 'slug' => 'zashhitnye-stekla', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/zashhitnye-stekla.jpg', 'parent_id' => 2],
            //(id = 15 в табл. 'categories')
            ['title' => 'IP-телефония', 'slug' => 'ip-telefoniya', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/ip-telefoniya.jpg', 'parent_id' => 2],
            //(id = 16 в табл. 'categories')
            ['title' => 'Док-станции', 'slug' => 'dok-stancii', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/dok-stancii.jpg', 'parent_id' => 2],
            //(id = 17 в табл. 'categories')
            ['title' => 'Кабели и розетки для стационарных телефонов', 'slug' => 'kabeli-i-rozetki-dlya-stacionarnyx-telefonov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/kabeli-i-rozetki-dlya-stacionarnyx-telefonov.jpg', 'parent_id' => 2],
            //(id = 18 в табл. 'categories')
            ['title' => 'Услуги для смартфонов и планшетов', 'slug' => 'uslugi-dlya-smartfonov-i-gadzetov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/smartfony-telefony/uslugi-dlya-smartfonov-i-gadzetov.jpg', 'parent_id' => 2],


            // КАТЕГОРИЯ: Планшеты и электронные книги (id = 19 в табл. 'categories')
            ['title' => 'Планшеты и электронные книги', 'slug' => 'plansety-i-elektronnye-knigi', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/plansety-i-elektronnye-knigi.jpg', 'parent_id' => 1],
            //ПОДКАТЕГОРИИ:
            //(id = 20 в табл. 'categories')
            ['title' => 'Планшеты', 'slug' => 'plansety', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/plansety.jpg', 'parent_id' => 19],
            //(id = 21 в табл. 'categories')
            ['title' => 'Электронные книги', 'slug' => 'elektronnye-knigi', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/elektronnye-knigi.jpg', 'parent_id' => 19],
            //(id = 22 в табл. 'categories')
            ['title' => 'Планшеты детские для рисования', 'slug' => 'plansety-detskie-dlya-risovaniya', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/plansety-detskie-dlya-risovaniya.jpg', 'parent_id' => 19],
            //(id = 23 в табл. 'categories')
            ['title' => 'Стилусы и перчатки', 'slug' => 'stilusy-i-percatki', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/stilusy-i-percatki.jpg', 'parent_id' => 19],
            //(id = 24 в табл. 'categories')
            ['title' => 'Чехлы для электронных книг', 'slug' => 'cexly-dlya-elektronnyx-knig', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/cexly-dlya-elektronnyx-knig.jpg', 'parent_id' => 19],
            //(id = 25 в табл. 'categories')
            ['title' => 'Подставки и штативы для планшетов', 'slug' => 'podstavki-i-stativy-dlya-plansetov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/podstavki-i-stativy-dlya-plansetov.jpg', 'parent_id' => 19],
            //(id = 26 в табл. 'categories')
            ['title' => 'Услуги для смартфонов и планшетов', 'slug' => 'uslugi-dlya-smartfonov-i-plansetov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/uslugi-dlya-smartfonov-i-plansetov.jpg', 'parent_id' => 19],
            //(id = 27 в табл. 'categories')
            ['title' => 'Чехлы и клавиатуры для планшетов', 'slug' => 'cexly-i-klaviatury-dlya-plansetov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/plansety-i-elektronnye-knigi/cexly-i-klaviatury-dlya-plansetov.jpg', 'parent_id' => 19],


            // КАТЕГОРИЯ: Портативный звук (id = 28 в табл. 'categories')
            ['title' => 'Портативный звук', 'slug' => 'portativnyi-zvuk', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/portativnyi-zvuk.jpg', 'parent_id' => 1],
            //ПОДКАТЕГОРИИ:
            //(id = 29 в табл. 'categories')
            ['title' => 'Наушники', 'slug' => 'nausniki', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/nausniki.jpg', 'parent_id' => 28],
            //(id = 30 в табл. 'categories')
            ['title' => 'Портативная акустика', 'slug' => 'portativnaya-akustika', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/portativnaya-akustika.jpg', 'parent_id' => 28],
            //(id = 31 в табл. 'categories')
            ['title' => 'MP3 плееры', 'slug' => 'mp3-pleery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/mp3-pleery.jpg', 'parent_id' => 28],
            //(id = 32 в табл. 'categories')
            ['title' => 'Диктофоны', 'slug' => 'diktofony', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/diktofony.jpg', 'parent_id' => 28],
            //(id = 33 в табл. 'categories')
            ['title' => 'Радиоприемники', 'slug' => 'radiopriemniki', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/radiopriemniki.jpg', 'parent_id' => 28],
            //(id = 34 в табл. 'categories')
            ['title' => 'Bluetooth гарнитуры', 'slug' => 'bluetooth-garnitury', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/bluetooth-garnitury.jpg', 'parent_id' => 28],
            //(id = 35 в табл. 'categories')
            ['title' => 'Радиочасы', 'slug' => 'radiocasy', 'image' => 'storage/images/category-images/smartfony-i-gadzety/portativnyi-zvuk/radiocasy.jpg', 'parent_id' => 28],


            // КАТЕГОРИЯ:  Гаджеты (id = 36 в табл. 'categories')
            ['title' => 'Гаджеты', 'slug' => 'gadzety', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/gadzety.jpg', 'parent_id' => 1],
            //ПОДКАТЕГОРИИ:
            //(id = 37 в табл. 'categories')
            ['title' => 'Умные часы', 'slug' => 'umnye-casy', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/umnye-casy.jpg', 'parent_id' => 36],
            //(id = 38 в табл. 'categories')
            ['title' => 'Фитнес трекеры', 'slug' => 'fitnes-trekery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/fitnes-trekery.jpg', 'parent_id' => 36],
            //(id = 39 в табл. 'categories')
            ['title' => 'Умные часы детские', 'slug' => 'umnye-casy-detskie', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/umnye-casy-detskie.jpg', 'parent_id' => 36],
            //(id = 40 в табл. 'categories')
            ['title' => 'Ремешки и браслеты', 'slug' => 'remeski-i-braslety', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/remeski-i-braslety.jpg', 'parent_id' => 36],
            //(id = 41 в табл. 'categories')
            ['title' => 'Очки виртуальной реальности', 'slug' => 'ocki-virtualnoi-realnosti', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/ocki-virtualnoi-realnosti.jpg', 'parent_id' => 36],
            //(id = 42 в табл. 'categories')
            ['title' => 'GPS-трекеры', 'slug' => 'gps-trekery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/gps-trekery.jpg', 'parent_id' => 36],
            //(id = 43 в табл. 'categories')
            ['title' => 'Аксессуары для умных часов', 'slug' => 'aksessuary-dlya-umnyx-casov', 'image' => 'storage/images/category-images/smartfony-i-gadzety/gadzety/aksessuary-dlya-umnyx-casov.jpg', 'parent_id' => 36],


            // КАТЕГОРИЯ:  Фото и видео (id = 44 в табл. 'categories')
            ['title' => 'Фото и видео', 'slug' => 'foto-i-video', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/foto-i-video.jpg', 'parent_id' => 1],
            //ПОДКАТЕГОРИИ:
            //(id = 45 в табл. 'categories') - одна запись! еще используется в категории `Телевизоры и аксессуары` - там используем запись из pivot 'category_product'
            ['title' => 'Элементы питания', 'slug' => 'elementy-pitaniya', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/elementy-pitaniya.jpg', 'parent_id' => 44],
            //(id = 46 в табл. 'categories')
            ['title' => 'Экшн камеры', 'slug' => 'eksn-kamery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/eksn-kamery.jpg', 'parent_id' => 44],
            //(id = 47 в табл. 'categories')
            ['title' => 'Принадлежности для фото', 'slug' => 'prinadlezhnosti-dlya-foto', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/prinadlezhnosti-dlya-foto.jpg', 'parent_id' => 44],
            //(id = 48 в табл. 'categories')
            ['title' => 'Фоторамки цифровые', 'slug' => 'fotoramki-cifrovye', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/fotoramki-cifrovye.jpg', 'parent_id' => 44],
            //(id = 49 в табл. 'categories')
            ['title' => 'Штативы', 'slug' => 'shtativy', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/shtativy.jpg', 'parent_id' => 44],
            //(id = 50 в табл. 'categories')
            ['title' => 'Зарядные устройства', 'slug' => 'zaryadnye-ustroystva', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/zaryadnye-ustroystva.jpg', 'parent_id' => 44],
            //(id = 51 в табл. 'categories')
            ['title' => 'Фотокамеры', 'slug' => 'fotokamery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/fotokamery.jpg', 'parent_id' => 44],
            //(id = 52 в табл. 'categories')
            ['title' => 'Фотопринтеры', 'slug' => 'fotoprintery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/fotoprintery.jpg', 'parent_id' => 44],
            //(id = 53 в табл. 'categories')
            ['title' => 'Бинокли и телескопы', 'slug' => 'binokli-i-teleskopy', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/binokli-i-teleskopy.jpg', 'parent_id' => 44],
            //(id = 54 в табл. 'categories')
            ['title' => 'Сумки, чехлы к фотоаппаратам и видеокамерам', 'slug' => 'sumki-chehly-k-fotoapparatam-i-videokameram', 'image' => 'storage/images/category-images/smartfony-i-gadzety/foto-i-video/sumki-chehly-k-fotoapparatam-i-videokameram.jpg', 'parent_id' => 44],


            // КАТЕГОРИЯ: Система умный дом  (id = 55 в табл. 'categories')
            ['title' => 'Система умный дом', 'slug' => 'sistema-umnyi-dom', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/sistema-umnyi-dom.jpg', 'parent_id' => 1],
            //ПОДКАТЕГОРИИ:
            //(id = 56 в табл. 'categories')
            ['title' => 'Умные колонки', 'slug' => 'umnye-kolonki', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnye-kolonki.jpg', 'parent_id' => 55],
            //(id = 57 в табл. 'categories')
            ['title' => 'Умный дом', 'slug' => 'umnyi-dom', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnyi-dom.jpg', 'parent_id' => 55],
            //(id = 58 в табл. 'categories')
            ['title' => 'Умное освещение', 'slug' => 'umnoe-osveshhenie', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnoe-osveshhenie.jpg', 'parent_id' => 55],
            //(id = 59 в табл. 'categories')
            ['title' => 'Умные датчики', 'slug' => 'umnye-datciki', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnye-datciki.jpg', 'parent_id' => 55],
            //(id = 60 в табл. 'categories')
            ['title' => 'Умные пульты', 'slug' => 'umnye-pulty', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnye-pulty.jpg', 'parent_id' => 55],
            //(id = 61 в табл. 'categories')
            ['title' => 'Умные розетки', 'slug' => 'umnye-rozetki', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnye-rozetki.jpg', 'parent_id' => 55],
            //(id = 62 в табл. 'categories')
            ['title' => 'Умный дом Xiaomi', 'slug' => 'umnyi-dom-xiaomi', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/umnyi-dom-xiaomi.jpg', 'parent_id' => 55],
            //(id = 63 в табл. 'categories')
            ['title' => 'Устройства сигнализации', 'slug' => 'ustroistva-signalizacii', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/ustroistva-signalizacii.jpg', 'parent_id' => 55],
            //(id = 64 в табл. 'categories')
            ['title' => 'Центры управления умным домом', 'slug' => 'centry-upravleniya-umnym-domom', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/centry-upravleniya-umnym-domom.jpg', 'parent_id' => 55],
            //(id = 65 в табл. 'categories')
            ['title' => 'IP камеры', 'slug' => 'ip-kamery', 'image' => 'storage/images/category-images/smartfony-i-gadzety/sistema-umnyi-dom/ip-kamery.jpg', 'parent_id' => 55],



            // ГРУППА КАТЕГОРИЙ: Телевизоры и видео (id = 66 в табл. 'categories')
            ['title' => 'Телевизоры и видео', 'slug' => 'televizory-i-video', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-video.jpg', 'parent_id' => null],

            // КАТЕГОРИЯ: Телевизоры и аксессуары (id = 67 в табл. 'categories')
            ['title' => 'Телевизоры и аксессуары', 'slug' => 'televizory-i-aksessuary', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/televizory-i-aksessuary.jpg', 'parent_id' => 66],
            //ПОДКАТЕГОРИИ:
            // (id = 68 в табл. 'categories')
            ['title' => 'Телевизоры', 'slug' => 'televizory', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/televizory.jpg', 'parent_id' => 67],
            // (id = 69 в табл. 'categories')
            ['title' => 'Кронштейны', 'slug' => 'kronsteiny', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/kronsteiny.jpg', 'parent_id' => 67],
            // (id = 70 в табл. 'categories')
            ['title' => 'Кабели и переходники', 'slug' => 'kabeli-i-perexodniki', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/kabeli-i-perexodniki.jpg', 'parent_id' => 67],
            // Элементы питания - уже! имеется! в категории `Фото и видео` — НЕ! создаём! новую! запись, используем существующую! через pivot 'category_product'
            // (id = 71 в табл. 'categories')
            ['title' => 'Приемники цифрового сигнала', 'slug' => 'priemniki-cifrovogo-signala', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/priemniki-cifrovogo-signala.jpg', 'parent_id' => 67],
            // (id = 72 в табл. 'categories')
            ['title' => 'Пульты ДУ', 'slug' => 'pulty-du', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/pulty-du.jpg', 'parent_id' => 67],
            // (id = 73 в табл. 'categories')
            ['title' => 'ТВ антенны', 'slug' => 'tv-antenny', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/tv-antenny.jpg', 'parent_id' => 67],
            // (id = 74 в табл. 'categories')
            ['title' => 'Проекторы', 'slug' => 'proektory', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/proektory.jpg', 'parent_id' => 67],
            // (id = 75 в табл. 'categories')
            ['title' => 'Приставки Smart TV', 'slug' => 'pristavki-smart-tv', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/pristavki-smart-tv.jpg', 'parent_id' => 67],
            // (id = 76 в табл. 'categories')
            ['title' => 'Экраны для проекторов', 'slug' => 'ekrany-dlya-proektorov', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/ekrany-dlya-proektorov.jpg', 'parent_id' => 67],
            // (id = 77 в табл. 'categories')
            ['title' => 'Стойки для телевизоров', 'slug' => 'stoiki-dlya-televizorov', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/stoiki-dlya-televizorov.jpg', 'parent_id' => 67],
            // (id = 78 в табл. 'categories')
            ['title' => 'ТВ штекеры', 'slug' => 'tv-stekery', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/tv-stekery.jpg', 'parent_id' => 67],
            // (id = 79 в табл. 'categories')
            ['title' => 'Онлайн-кинотеатры', 'slug' => 'onlain-kinoteatry', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/onlain-kinoteatry.jpg', 'parent_id' => 67],
            // (id = 80 в табл. 'categories')
            ['title' => 'Рамки для телевизоров', 'slug' => 'ramki-dlya-televizorov', 'image' => 'storage/images/category-images/televizory-i-video/televizory-i-aksessuary/ramki-dlya-televizorov.jpg', 'parent_id' => 67],


            // КАТЕГОРИЯ: Аудиотехника и аксессуары (id = 81 в табл. 'categories')
            ['title' => 'Аудиотехника и аксессуары', 'slug' => 'audiotexnika-i-aksessuary', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/audiotexnika-i-aksessuary.jpg', 'parent_id' => 66],
            //ПОДКАТЕГОРИИ: 
            // (id = 82 в табл. 'categories')
            ['title' => 'Портативная акустика и колонки для вечеринок', 'slug' => 'portativnaya-akustika-i-kolonki', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/portativnaya-akustika-i-kolonki.jpg', 'parent_id' => 81],
            // (id = 83 в табл. 'categories')
            ['title' => 'Звуковые панели и саундбары', 'slug' => 'zvukovye-paneli-i-saundbary', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/zvukovye-paneli-i-saundbary.jpg', 'parent_id' => 81],
            // (id = 84 в табл. 'categories')
            ['title' => 'Микрофоны', 'slug' => 'mikrofony', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/mikrofony.jpg', 'parent_id' => 81],
            // (id = 85 в табл. 'categories')
            ['title' => 'Виниловые проигрыватели', 'slug' => 'vinilovye-proigryvateli', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/vinilovye-proigryvateli.jpg', 'parent_id' => 81],
            // (id = 86 в табл. 'categories')
            ['title' => 'Музыкальные центры', 'slug' => 'muzykalnye-centry', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/muzykalnye-centry.jpg', 'parent_id' => 81],
            // (id = 87 в табл. 'categories')
            ['title' => 'Адаптеры', 'slug' => 'adaptery', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/adaptery.jpg', 'parent_id' => 81],
            // (id = 88 в табл. 'categories')
            ['title' => 'Акустика Hi-Fi', 'slug' => 'akustika-hi-fi', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/akustika-hi-fi.jpg', 'parent_id' => 81],
            // (id = 89 в табл. 'categories')
            ['title' => 'Домашние кинотеатры', 'slug' => 'domasnie-kinoteatry', 'image' => 'storage/images/category-images/televizory-i-video/audiotexnika-i-aksessuary/domasnie-kinoteatry.jpg', 'parent_id' => 81],

            // КАТЕГОРИЯ: Консоли и видеоигры (id = 90 в табл. 'categories') 
            ['title' => 'Консоли и видеоигры', 'slug' => 'konsoli-i-videoigry', 'image' => 'storage/images/category-images/televizory-i-video/konsoli-i-videoigry/konsoli-i-videoigry.jpg', 'parent_id' => 66],
            //ПОДКАТЕГОРИИ:
            // (id = 91 в табл. 'categories')
            ['title' => 'Видеоигры', 'slug' => 'videoigry', 'image' => 'storage/images/category-images/televizory-i-video/konsoli-i-videoigry/videoigry.jpg', 'parent_id' => 90],
            // (id = 92 в табл. 'categories')
            ['title' => 'Игровые консоли', 'slug' => 'igrovye-konsoli', 'image' => 'storage/images/category-images/televizory-i-video/konsoli-i-videoigry/igrovye-konsoli.jpg', 'parent_id' => 90],
            // (id = 93 в табл. 'categories')
            ['title' => 'Аксессуары для игровых консолей', 'slug' => 'aksessuary-dlya-igrovyx-konsolei', 'image' => 'storage/images/category-images/televizory-i-video/konsoli-i-videoigry/aksessuary-dlya-igrovyx-konsolei.jpg', 'parent_id' => 90],
            // (id = 94 в табл. 'categories')
            ['title' => 'Услуги для игровых консолей', 'slug' => 'uslugi-dlya-igrovyx-konsolei', 'image' => 'storage/images/category-images/televizory-i-video/konsoli-i-videoigry/uslugi-dlya-igrovyx-konsolei.jpg', 'parent_id' => 90],



            // Ниже ПУСТЫЕ ГРУППЫ КАТЕГОРИЙ, содержащие только `title`, `slug` и `image` - ПРОСТО ДЛЯ НАПОЛНЕНИЯ КАТАЛОГА

            // ГРУППА КАТЕГОРИЙ: Ноутбуки и компьютеры (id = 95 в табл. 'categories')
            ['title' => 'Ноутбуки и компьютеры', 'slug' => 'noutbuki-i-kompyutery', 'image' => 'storage/images/category-images/noutbuki-i-kompyutery/noutbuki-i-kompyutery.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Техника для кухни (id = 96 в табл. 'categories')
            ['title' => 'Техника для кухни', 'slug' => 'tehnika-dlya-kuhni', 'image' => 'storage/images/category-images/tehnika-dlya-kuhni/tehnika-dlya-kuhni.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Техника для дома (id = 97 в табл. 'categories')
            ['title' => 'Техника для дома', 'slug' => 'tehnika-dlya-doma', 'image' => 'storage/images/category-images/tehnika-dlya-doma/tehnika-dlya-doma.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Инструменты и садовая техника (id = 98 в табл. 'categories')
            ['title' => 'Инструменты и садовая техника', 'slug' => 'instrumenty-i-sadovaya-tehnika', 'image' => 'storage/images/category-images/instrumenty-i-sadovaya-tehnika/instrumenty-i-sadovaya-tehnika.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Товары для дома (id = 99 в табл. 'categories')
            ['title' => 'Товары для дома', 'slug' => 'tovary-dlya-doma', 'image' => 'storage/images/category-images/tovary-dlya-doma/tovary-dlya-doma.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Красота и здоровье (id = 100 в табл. 'categories')
            ['title' => 'Красота и здоровье', 'slug' => 'krasota-i-zdorove', 'image' => 'storage/images/category-images/krasota-i-zdorove/krasota-i-zdorove.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Детский мир (id = 101 в табл. 'categories')
            ['title' => 'Детский мир', 'slug' => 'detskiy-mir', 'image' => 'storage/images/category-images/detskiy-mir/detskiy-mir.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Спорт, отдых, развлечения (id = 102 в табл. 'categories')
            ['title' => 'Спорт, отдых, развлечения', 'slug' => 'sport-otdyh-razvlecheniya', 'image' => 'storage/images/category-images/sport-otdyh-razvlecheniya/sport-otdyh-razvlecheniya.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Автотовары (id = 103 в табл. 'categories')
            ['title' => 'Автотовары', 'slug' => 'avtotovary', 'image' => 'storage/images/category-images/avtotovary/avtotovary.jpg', 'parent_id' => null],

            // ГРУППА КАТЕГОРИЙ: Услуги и сервисы (id = 104 в табл. 'categories')
            ['title' => 'Услуги и сервисы', 'slug' => 'uslugi-i-servisy', 'image' => 'storage/images/category-images/uslugi-i-servisy/uslugi-i-servisy.jpg', 'parent_id' => null],


        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
