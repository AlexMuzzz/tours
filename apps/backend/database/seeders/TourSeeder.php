<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Services\TourService;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $tours = [
            [
                'tour' => [
                    'title' => 'Тур по Алтаю',
                    'slug' => 'tur-po-altayu',
                    'short_description' => 'Горные тропы, бирюзовые реки и перевалы с панорамами Алтая.',
                    'description' => 'Семидневное путешествие по Алтаю для тех, кто любит простор, горный воздух и активные прогулки без перегруженного темпа. Маршрут сочетает живописные переезды, треккинг к обзорным точкам, знакомство с долинами Катуни и остановки в местах, где особенно хорошо чувствуется масштаб региона. Тур подойдёт путешественникам, которым важно увидеть настоящую горную природу России, но при этом сохранить комфортный ритм, понятную логистику и время на отдых.',
                    'duration_days' => 7,
                    'category' => 'hiking',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Панорама алтайских гор', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Бирюзовая река в долине Алтая', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Лесная тропа в Алтае', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-10', 'end_date' => '2026-06-16', 'price' => 48900, 'currency' => 'RUB', 'available_seats' => 12],
                    ['start_date' => '2026-07-08', 'end_date' => '2026-07-14', 'price' => 51900, 'currency' => 'RUB', 'available_seats' => 10],
                    ['start_date' => '2026-08-12', 'end_date' => '2026-08-18', 'price' => 52900, 'currency' => 'RUB', 'available_seats' => 8],
                ],
                'route_points' => [
                    ['title' => 'Горно-Алтайск', 'description' => 'Встреча группы, брифинг и подготовка к выезду в горную часть маршрута.', 'latitude' => 51.9581, 'longitude' => 85.9603, 'sort_order' => 1],
                    ['title' => 'Чемал', 'description' => 'Первый день с прогулками у Катуни, обзорными точками и мягкой акклиматизацией.', 'latitude' => 51.4111, 'longitude' => 86.0056, 'sort_order' => 2],
                    ['title' => 'Акташ', 'description' => 'Переезд в сторону высокогорья и остановки на самых эффектных панорамах маршрута.', 'latitude' => 50.3136, 'longitude' => 87.7337, 'sort_order' => 3],
                    ['title' => 'Курайская степь', 'description' => 'Широкие открытые пространства, снежные вершины и одна из самых узнаваемых картин Алтая.', 'latitude' => 50.2311, 'longitude' => 87.9368, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Большое путешествие по Байкалу',
                    'slug' => 'bolshoe-puteshestvie-po-baikalu',
                    'short_description' => 'Островные пейзажи, скалы, вода и спокойный ритм вокруг великого озера.',
                    'description' => 'Маршрут по Байкалу создан для тех, кто хочет увидеть озеро не в формате спешной экскурсии, а как цельное путешествие с природой, местной кухней и ощущением пространства. За несколько дней гости проходят через главные локации побережья, встречают рассветы над водой, гуляют по скальным обзорным площадкам и знакомятся с ритмом байкальских посёлков. Тур подойдёт тем, кто ищет баланс между красивыми видами, мягкой активностью и расслабленной атмосферой у воды.',
                    'duration_days' => 6,
                    'category' => 'nature',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Байкальский берег', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Смотровая площадка над озером', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Природный заповедник рядом с Байкалом', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-18', 'end_date' => '2026-06-23', 'price' => 42900, 'currency' => 'RUB', 'available_seats' => 14],
                    ['start_date' => '2026-07-20', 'end_date' => '2026-07-25', 'price' => 44900, 'currency' => 'RUB', 'available_seats' => 10],
                    ['start_date' => '2026-08-17', 'end_date' => '2026-08-22', 'price' => 46900, 'currency' => 'RUB', 'available_seats' => 10],
                ],
                'route_points' => [
                    ['title' => 'Иркутск', 'description' => 'Старт маршрута, знакомство с регионом и выезд к берегу Байкала.', 'latitude' => 52.2869, 'longitude' => 104.3050, 'sort_order' => 1],
                    ['title' => 'Листвянка', 'description' => 'Прогулки по набережной, местный рынок и первые виды на озеро.', 'latitude' => 51.8532, 'longitude' => 104.8693, 'sort_order' => 2],
                    ['title' => 'Ольхон', 'description' => 'Островные ландшафты, просторные степи и знаменитые скальные мысы.', 'latitude' => 53.1936, 'longitude' => 107.3381, 'sort_order' => 3],
                    ['title' => 'Мыс Хобой', 'description' => 'Одна из самых впечатляющих точек северного Ольхона с открытым видом на Байкал.', 'latitude' => 53.4605, 'longitude' => 107.7923, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Открывая Дагестан',
                    'slug' => 'otkryvaya-dagestan',
                    'short_description' => 'Каньоны, древние аулы, каспийское побережье и насыщенная культурная программа.',
                    'description' => 'Этот тур по Дагестану собирает в одном маршруте сразу несколько характеров региона: величественные каньоны, горные дороги, исторические поселения и живое побережье Каспия. Поездка подойдёт тем, кто хочет не просто посмотреть красивые точки, а прочувствовать ритм местной культуры, кухни и гостеприимства. Программа выстроена так, чтобы каждый день давал новый визуальный и эмоциональный акцент, но при этом оставлял пространство для спокойного знакомства с местом.',
                    'duration_days' => 5,
                    'category' => 'culture',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Горы Дагестана', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Каменный аул в Дагестане', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Каспийское побережье', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-05', 'end_date' => '2026-06-09', 'price' => 36900, 'currency' => 'RUB', 'available_seats' => 16],
                    ['start_date' => '2026-07-03', 'end_date' => '2026-07-07', 'price' => 38900, 'currency' => 'RUB', 'available_seats' => 12],
                    ['start_date' => '2026-09-11', 'end_date' => '2026-09-15', 'price' => 39500, 'currency' => 'RUB', 'available_seats' => 10],
                ],
                'route_points' => [
                    ['title' => 'Махачкала', 'description' => 'Встреча группы, первое знакомство с кухней и вечерний выезд к морю.', 'latitude' => 42.9849, 'longitude' => 47.5047, 'sort_order' => 1],
                    ['title' => 'Сулакский каньон', 'description' => 'Главная природная точка программы с панорамами и водной бирюзой внизу.', 'latitude' => 43.0161, 'longitude' => 46.8296, 'sort_order' => 2],
                    ['title' => 'Гуниб', 'description' => 'Горное село с насыщенной историей, смотровыми площадками и атмосферой старого Кавказа.', 'latitude' => 42.3876, 'longitude' => 46.9658, 'sort_order' => 3],
                    ['title' => 'Дербент', 'description' => 'Финальный акцент маршрута: древний город, крепость и прогулки по Каспийскому побережью.', 'latitude' => 42.0678, 'longitude' => 48.2899, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Экспедиция на Камчатку',
                    'slug' => 'ekspeditsiya-na-kamchatku',
                    'short_description' => 'Вулканы, океан, термальные зоны и большое приключение на краю страны.',
                    'description' => 'Камчатка в этом маршруте показана как территория сильных природных впечатлений: вулканические массивы, чёрные пляжи, океанские ветра и контраст между суровым ландшафтом и редким человеческим присутствием. Тур подойдёт путешественникам, которые хотят за одну поездку получить ощущение настоящей экспедиции, но без экстремального уровня сложности. Программа сочетает выезды к главным природным объектам, обзорные точки, термальные остановки и время на неспешное наблюдение за камчатскими пейзажами.',
                    'duration_days' => 9,
                    'category' => 'adventure',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Вулканический ландшафт Камчатки', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Горный хребет Камчатки', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Долина и вершины Камчатки', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-07-14', 'end_date' => '2026-07-22', 'price' => 119000, 'currency' => 'RUB', 'available_seats' => 8],
                    ['start_date' => '2026-08-18', 'end_date' => '2026-08-26', 'price' => 124000, 'currency' => 'RUB', 'available_seats' => 7],
                    ['start_date' => '2026-09-08', 'end_date' => '2026-09-16', 'price' => 127000, 'currency' => 'RUB', 'available_seats' => 6],
                ],
                'route_points' => [
                    ['title' => 'Петропавловск-Камчатский', 'description' => 'Старт путешествия и знакомство с городом, который смотрит прямо на Авачинскую бухту.', 'latitude' => 53.0370, 'longitude' => 158.6559, 'sort_order' => 1],
                    ['title' => 'Авачинская бухта', 'description' => 'Океанские виды, прогулки вдоль берега и первый большой визуальный акцент поездки.', 'latitude' => 52.9736, 'longitude' => 158.7306, 'sort_order' => 2],
                    ['title' => 'Мутновский район', 'description' => 'Геотермальные зоны, фумаролы и драматичный рельеф рядом с вулканом.', 'latitude' => 52.4531, 'longitude' => 158.1953, 'sort_order' => 3],
                    ['title' => 'Халактырский пляж', 'description' => 'Чёрный вулканический песок, Тихий океан и ощущение края земли.', 'latitude' => 52.9867, 'longitude' => 158.9759, 'sort_order' => 4],
                    ['title' => 'Паратунка', 'description' => 'Термальный финал активной части маршрута и время на восстановление.', 'latitude' => 52.9683, 'longitude' => 158.2581, 'sort_order' => 5],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Городской уикенд в Санкт-Петербурге',
                    'slug' => 'gorodskoi-uikend-v-sankt-peterburge',
                    'short_description' => 'Музеи, каналы, парадные проспекты и красивый ритм северной столицы.',
                    'description' => 'Этот короткий маршрут по Санкт-Петербургу собран как насыщенный, но лёгкий city-break: без спешки, с узнаваемыми городскими видами, продуманными прогулками и временем на собственный ритм. За несколько дней гости проходят по главным символам города, смотрят на Неву и каналы, посещают музейные пространства и получают удобный баланс между экскурсионной частью и свободными часами для кофеен, книжных и вечерних прогулок. Тур подойдёт тем, кто хочет качественно провести выходные в одном из самых атмосферных городов страны.',
                    'duration_days' => 3,
                    'category' => 'city',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Набережная Санкт-Петербурга', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Исторические улицы Петербурга', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Каналы и мосты города', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-12', 'end_date' => '2026-06-14', 'price' => 24900, 'currency' => 'RUB', 'available_seats' => 20],
                    ['start_date' => '2026-07-17', 'end_date' => '2026-07-19', 'price' => 26900, 'currency' => 'RUB', 'available_seats' => 20],
                    ['start_date' => '2026-08-14', 'end_date' => '2026-08-16', 'price' => 27900, 'currency' => 'RUB', 'available_seats' => 18],
                ],
                'route_points' => [
                    ['title' => 'Невский проспект', 'description' => 'Стартовая прогулка по главной городской оси с архитектурой и витринами.', 'latitude' => 59.9343, 'longitude' => 30.3351, 'sort_order' => 1],
                    ['title' => 'Эрмитаж', 'description' => 'Главный музейный акцент поездки и погружение в масштаб имперского Петербурга.', 'latitude' => 59.9398, 'longitude' => 30.3146, 'sort_order' => 2],
                    ['title' => 'Петропавловская крепость', 'description' => 'Исторический остров, панорама на Неву и один из лучших видов на центр города.', 'latitude' => 59.9500, 'longitude' => 30.3167, 'sort_order' => 3],
                    ['title' => 'Причал для прогулки по каналам', 'description' => 'Вечерний акцент маршрута с видом на воду, мосты и фасады старого города.', 'latitude' => 59.9386, 'longitude' => 30.3086, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Гастрономический тур по Калининграду',
                    'slug' => 'gastronomicheskii-tur-po-kaliningradu',
                    'short_description' => 'Балтийские вкусы, локальные сыроварни и прогулки между городом и морем.',
                    'description' => 'Калининград в этом маршруте раскрывается через еду, атмосферу старых кварталов и близость Балтийского моря. Тур подойдёт тем, кто любит путешествия с хорошим вкусом: рынки, дегустации, локальные производители, ужины с морским акцентом и неспешные прогулки по побережью. Программа выстроена так, чтобы гастрономические впечатления не перегружали поездку, а работали вместе с городскими и природными точками, создавая цельный и очень комфортный формат отдыха.',
                    'duration_days' => 4,
                    'category' => 'gastro',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Ресторанный стол в Калининграде', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Дегустация локальных продуктов', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Ужин у Балтийского моря', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-04-17', 'end_date' => '2026-04-20', 'price' => 38900, 'currency' => 'RUB', 'available_seats' => 16],
                    ['start_date' => '2026-05-22', 'end_date' => '2026-05-25', 'price' => 40900, 'currency' => 'RUB', 'available_seats' => 14],
                    ['start_date' => '2026-09-18', 'end_date' => '2026-09-21', 'price' => 42900, 'currency' => 'RUB', 'available_seats' => 12],
                ],
                'route_points' => [
                    ['title' => 'Калининград', 'description' => 'Старт маршрута, рынок, исторические кварталы и первый гастрономический ужин.', 'latitude' => 54.7104, 'longitude' => 20.4522, 'sort_order' => 1],
                    ['title' => 'Зеленоградск', 'description' => 'Обед у моря, прогулка по променаду и знакомство с балтийским настроением.', 'latitude' => 54.9600, 'longitude' => 20.4753, 'sort_order' => 2],
                    ['title' => 'Куршская коса', 'description' => 'Природная остановка с видами на дюны и локальными рыбными блюдами.', 'latitude' => 55.1516, 'longitude' => 20.8376, 'sort_order' => 3],
                    ['title' => 'Сыроварня', 'description' => 'Визит к местным производителям и дегустация региональных продуктов.', 'latitude' => 54.7150, 'longitude' => 20.6142, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Охота за северным сиянием в Мурманске',
                    'slug' => 'okhota-za-severnym-siyaniem-v-murmanske',
                    'short_description' => 'Арктическая тундра, снежные пейзажи и шанс увидеть небо, которое запоминается навсегда.',
                    'description' => 'Зимний маршрут вокруг Мурманска рассчитан на путешественников, которым хочется прочувствовать северную атмосферу без экстремального формата. Главная цель поездки — северное сияние, но программа не сводится только к ночным выездам: в неё включены тундровые ландшафты, побережье Баренцева моря, культурные остановки и спокойные северные вечера. Тур хорошо подходит для первой арктической поездки, когда хочется сочетать яркое впечатление, понятную логистику и по-настоящему зимний характер маршрута.',
                    'duration_days' => 4,
                    'category' => 'winter',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1483347756197-71ef80e95f73?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1515238152791-8216bfdf89a7?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Северное сияние над снежным пейзажем', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Заснеженные просторы Кольского полуострова', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1547448415-e9f5b28e570d?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Арктическая зимняя дорога', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-01-15', 'end_date' => '2026-01-18', 'price' => 45900, 'currency' => 'RUB', 'available_seats' => 14],
                    ['start_date' => '2026-02-12', 'end_date' => '2026-02-15', 'price' => 47900, 'currency' => 'RUB', 'available_seats' => 12],
                    ['start_date' => '2026-03-12', 'end_date' => '2026-03-15', 'price' => 46900, 'currency' => 'RUB', 'available_seats' => 10],
                ],
                'route_points' => [
                    ['title' => 'Мурманск', 'description' => 'Прилёт, знакомство с городом и вводный брифинг перед арктическими выездами.', 'latitude' => 68.9707, 'longitude' => 33.0749, 'sort_order' => 1],
                    ['title' => 'Териберка', 'description' => 'Побережье Баренцева моря, фактурные скалы и ощущение настоящего Севера.', 'latitude' => 69.1609, 'longitude' => 35.1450, 'sort_order' => 2],
                    ['title' => 'Aurora Camp', 'description' => 'Ночной выезд в тёмную зону, где шансы увидеть северное сияние особенно высоки.', 'latitude' => 68.9022, 'longitude' => 33.2621, 'sort_order' => 3],
                    ['title' => 'Саамская деревня', 'description' => 'Культурная остановка с локальными историями и зимними активностями.', 'latitude' => 68.4244, 'longitude' => 33.2556, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Треккинг в Приэльбрусье',
                    'slug' => 'trekking-v-prielbruse',
                    'short_description' => 'Альпийские луга, ледники, канатные дороги и большие виды Кавказа.',
                    'description' => 'Маршрут в Приэльбрусье подойдёт тем, кто хочет получить сильное горное впечатление без формата технического восхождения. Программа строится вокруг треккинговых выходов, акклиматизационных маршрутов, подъёмов к обзорным площадкам и постепенного знакомства с рельефом Кавказа. Здесь много воздуха, простора и ясного ощущения высоты, но при этом путешествие остаётся комфортным и понятным по нагрузке. Тур особенно хорош для тех, кто любит горы как пространство силы и тишины, а не только как спортивную цель.',
                    'duration_days' => 8,
                    'category' => 'hiking',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Панорама Эльбруса', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Горная тропа в Приэльбрусье', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Лесной подход к маршруту', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-24', 'end_date' => '2026-07-01', 'price' => 68900, 'currency' => 'RUB', 'available_seats' => 12],
                    ['start_date' => '2026-07-22', 'end_date' => '2026-07-29', 'price' => 72900, 'currency' => 'RUB', 'available_seats' => 10],
                    ['start_date' => '2026-09-02', 'end_date' => '2026-09-09', 'price' => 75900, 'currency' => 'RUB', 'available_seats' => 8],
                ],
                'route_points' => [
                    ['title' => 'Минеральные Воды', 'description' => 'Точка прилёта и старт логистики к подножию Кавказских гор.', 'latitude' => 44.2088, 'longitude' => 43.1383, 'sort_order' => 1],
                    ['title' => 'Терскол', 'description' => 'Базовый посёлок маршрута, откуда удобно выходить на основные треки.', 'latitude' => 43.2574, 'longitude' => 42.5144, 'sort_order' => 2],
                    ['title' => 'Чегет', 'description' => 'Акклиматизационный день с открытыми видами на ледники и склоны Эльбруса.', 'latitude' => 43.2447, 'longitude' => 42.4720, 'sort_order' => 3],
                    ['title' => 'Гарабаши', 'description' => 'Высотная точка с сильной панорамой и ощущением настоящего большого Кавказа.', 'latitude' => 43.2895, 'longitude' => 42.4766, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Пляжный отдых на Каспийском море',
                    'slug' => 'plyazhnyi-otdykh-na-kaspiiskom-more',
                    'short_description' => 'Тёплое море, спокойные пляжи, прогулки у воды и мягкий курортный ритм.',
                    'description' => 'Этот тур создан для тех, кто ищет именно отдых у моря: без сложных переходов, без напряжённой экскурсионной гонки и с достаточным временем для купания, прогулок по побережью и спокойных вечеров на набережной. Маршрут проходит вдоль каспийского берега и сочетает тёплый климат, пляжную часть, локальную кухню и мягкий темп, в котором действительно можно переключиться и восстановиться. Это хороший вариант для летней поездки, когда хочется солнца, воды и простого ощущения отпуска.',
                    'duration_days' => 5,
                    'category' => 'nature',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Пляж и тёплое море', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1493558103817-58b2924bce98?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Прогулка вдоль побережья', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Закат у воды', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-09', 'end_date' => '2026-06-13', 'price' => 43900, 'currency' => 'RUB', 'available_seats' => 16],
                    ['start_date' => '2026-07-14', 'end_date' => '2026-07-18', 'price' => 46900, 'currency' => 'RUB', 'available_seats' => 14],
                    ['start_date' => '2026-08-18', 'end_date' => '2026-08-22', 'price' => 48900, 'currency' => 'RUB', 'available_seats' => 12],
                ],
                'route_points' => [
                    ['title' => 'Дербент', 'description' => 'Исторический город и мягкий старт морского маршрута.', 'latitude' => 42.0678, 'longitude' => 48.2899, 'sort_order' => 1],
                    ['title' => 'Каспийский пляж', 'description' => 'Главная часть программы: море, купание, отдых и свободное время у воды.', 'latitude' => 42.0532, 'longitude' => 48.3046, 'sort_order' => 2],
                    ['title' => 'Набережная Дербента', 'description' => 'Вечерние прогулки вдоль побережья и закаты над Каспием.', 'latitude' => 42.0584, 'longitude' => 48.2924, 'sort_order' => 3],
                    ['title' => 'Кафе у моря', 'description' => 'Спокойный обед с видом на воду и локальными блюдами южного побережья.', 'latitude' => 42.0610, 'longitude' => 48.2978, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Золотое кольцо России',
                    'slug' => 'zolotoe-koltso-rossii',
                    'short_description' => 'Старинные города, монастыри, белокаменные соборы и классическая культурная поездка.',
                    'description' => 'Путешествие по Золотому кольцу — это возможность увидеть привычную школьную историю как живое пространство: с городскими стенами, монастырями, древними площадями и медленным русским ритмом небольших исторических центров. Маршрут подойдёт тем, кто ценит архитектуру, культурный контекст, музейные остановки и не хочет уезжать слишком далеко ради насыщенной поездки. Программа выстроена так, чтобы каждый город добавлял новый слой к общему впечатлению, а дорога между ними оставалась комфортной и логичной.',
                    'duration_days' => 5,
                    'category' => 'culture',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1520637836862-4d197d17c93a?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1513326738677-b964603b136d?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Древнерусская архитектура', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Площадь исторического города', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1549897164-78d2677dcd9b?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Стены старинного монастыря', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-05-13', 'end_date' => '2026-05-17', 'price' => 34900, 'currency' => 'RUB', 'available_seats' => 20],
                    ['start_date' => '2026-06-10', 'end_date' => '2026-06-14', 'price' => 36900, 'currency' => 'RUB', 'available_seats' => 18],
                    ['start_date' => '2026-08-19', 'end_date' => '2026-08-23', 'price' => 38900, 'currency' => 'RUB', 'available_seats' => 16],
                ],
                'route_points' => [
                    ['title' => 'Владимир', 'description' => 'Белокаменные ворота, соборы и первый исторический акцент маршрута.', 'latitude' => 56.1291, 'longitude' => 40.4066, 'sort_order' => 1],
                    ['title' => 'Суздаль', 'description' => 'Неспешный город-музей с монастырями, куполами и тихими улочками.', 'latitude' => 56.4194, 'longitude' => 40.4496, 'sort_order' => 2],
                    ['title' => 'Ярославль', 'description' => 'Волжские набережные и более городской слой культурного маршрута.', 'latitude' => 57.6261, 'longitude' => 39.8845, 'sort_order' => 3],
                    ['title' => 'Сергиев Посад', 'description' => 'Финальная точка с сильным духовным и архитектурным настроением.', 'latitude' => 56.3063, 'longitude' => 38.1333, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Курортный отдых в Сочи',
                    'slug' => 'kurortnyi-otdykh-v-sochi',
                    'short_description' => 'Тёплое Чёрное море, пляжи, пальмы и расслабленный отпуск у воды.',
                    'description' => 'Этот маршрут собран для тех, кто хочет классический летний отдых у моря без перегруженной экскурсионной части. В программе много времени на пляж, купание, прогулки по набережной и мягкий южный ритм, в котором можно по-настоящему переключиться. Поездка подойдёт путешественникам, которым важны тёплая вода, солнце, курортная атмосфера и комфортный баланс между свободным временем и короткими выездами вдоль побережья.',
                    'duration_days' => 6,
                    'category' => 'nature',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Пляж на Чёрном море', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Южная набережная Сочи', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Закат у моря', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-16', 'end_date' => '2026-06-21', 'price' => 55900, 'currency' => 'RUB', 'available_seats' => 16],
                    ['start_date' => '2026-07-21', 'end_date' => '2026-07-26', 'price' => 58900, 'currency' => 'RUB', 'available_seats' => 14],
                    ['start_date' => '2026-08-25', 'end_date' => '2026-08-30', 'price' => 61900, 'currency' => 'RUB', 'available_seats' => 12],
                ],
                'route_points' => [
                    ['title' => 'Сочи', 'description' => 'Прилёт и мягкий старт отпуска с первыми прогулками у моря.', 'latitude' => 43.5855, 'longitude' => 39.7231, 'sort_order' => 1],
                    ['title' => 'Центральный пляж', 'description' => 'Купание, отдых на берегу и свободное время на пляжный ритм.', 'latitude' => 43.5775, 'longitude' => 39.7195, 'sort_order' => 2],
                    ['title' => 'Имеретинская набережная', 'description' => 'Длинная прогулка вдоль побережья с кафе, морским воздухом и закатами.', 'latitude' => 43.4058, 'longitude' => 39.9543, 'sort_order' => 3],
                    ['title' => 'Роза Хутор', 'description' => 'Короткий выезд в горы как дополнительный акцент без потери курортного темпа.', 'latitude' => 43.6708, 'longitude' => 40.2968, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Семейные каникулы в Анапе',
                    'slug' => 'semeinye-kanikuly-v-anape',
                    'short_description' => 'Песчаные пляжи, тёплое море и спокойный семейный формат отдыха.',
                    'description' => 'Анапа в этом маршруте показана как удобное направление для тех, кто ищет спокойный пляжный отдых у моря с понятной логистикой и мягким темпом. Здесь много солнца, длинные песчаные пляжи, тёплая вода и формат поездки, где одинаково комфортно и взрослым, и детям. Тур особенно хорошо подходит для летнего отпуска, когда хочется купаться, гулять вдоль берега и не превращать поездку в череду переездов.',
                    'duration_days' => 5,
                    'category' => 'nature',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1493558103817-58b2924bce98?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Песчаный пляж Анапы', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Море и летний курорт', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Тихий берег у воды', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-23', 'end_date' => '2026-06-27', 'price' => 47900, 'currency' => 'RUB', 'available_seats' => 18],
                    ['start_date' => '2026-07-28', 'end_date' => '2026-08-01', 'price' => 50900, 'currency' => 'RUB', 'available_seats' => 16],
                    ['start_date' => '2026-08-18', 'end_date' => '2026-08-22', 'price' => 52900, 'currency' => 'RUB', 'available_seats' => 14],
                ],
                'route_points' => [
                    ['title' => 'Анапа', 'description' => 'Заселение и знакомство с курортной частью города.', 'latitude' => 44.8949, 'longitude' => 37.3166, 'sort_order' => 1],
                    ['title' => 'Песчаный пляж', 'description' => 'Основная часть программы: море, купание и свободное время у воды.', 'latitude' => 44.8919, 'longitude' => 37.3027, 'sort_order' => 2],
                    ['title' => 'Высокий берег', 'description' => 'Спокойные прогулки с морскими видами и вечерним светом над побережьем.', 'latitude' => 44.8864, 'longitude' => 37.3053, 'sort_order' => 3],
                    ['title' => 'Винодельня', 'description' => 'Небольшой дополнительный выезд для тех, кто хочет добавить южный гастрономический акцент.', 'latitude' => 44.9785, 'longitude' => 37.3638, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Балтийский релакс в Светлогорске',
                    'slug' => 'baltiiskii-relaks-v-svetlogorske',
                    'short_description' => 'Море, сосны, променад и спокойный отдых на балтийском побережье.',
                    'description' => 'Этот балтийский маршрут подойдёт тем, кто хочет море и побережье не в жарком южном формате, а в более спокойном, атмосферном и размеренном ритме. Светлогорск даёт сочетание моря, соснового воздуха, длинных прогулок по променаду и уютной курортной среды без суеты большого города. Тур хорош для тех, кто ищет именно передышку у воды, красивые виды и мягкий североевропейский характер отдыха.',
                    'duration_days' => 4,
                    'category' => 'nature',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Балтийское побережье', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Променад у моря', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Сосновый парк у берега', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-05-29', 'end_date' => '2026-06-01', 'price' => 41900, 'currency' => 'RUB', 'available_seats' => 14],
                    ['start_date' => '2026-07-10', 'end_date' => '2026-07-13', 'price' => 43900, 'currency' => 'RUB', 'available_seats' => 12],
                    ['start_date' => '2026-08-21', 'end_date' => '2026-08-24', 'price' => 45900, 'currency' => 'RUB', 'available_seats' => 12],
                ],
                'route_points' => [
                    ['title' => 'Светлогорск', 'description' => 'Курортный старт с прогулкой по тихим улочкам и первыми видами на море.', 'latitude' => 54.9433, 'longitude' => 20.1515, 'sort_order' => 1],
                    ['title' => 'Променад', 'description' => 'Прогулки вдоль побережья, морской воздух и неторопливый ритм отдыха.', 'latitude' => 54.9466, 'longitude' => 20.1528, 'sort_order' => 2],
                    ['title' => 'Янтарный', 'description' => 'Выезд к широкому берегу и одной из самых красивых пляжных линий региона.', 'latitude' => 54.8693, 'longitude' => 19.9407, 'sort_order' => 3],
                    ['title' => 'Озеро Тихое', 'description' => 'Спокойный зелёный финал с водой, соснами и камерной атмосферой курорта.', 'latitude' => 54.9447, 'longitude' => 20.1569, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Ладожские шхеры на каяках',
                    'slug' => 'ladozhskie-shkhery-na-kayakakh',
                    'short_description' => 'Острова, скалы, вода и активное приключение среди северных пейзажей.',
                    'description' => 'Этот маршрут по Ладожским шхерам рассчитан на тех, кто любит воду и северную природу, но не ищет пляжный или курортный формат. Поездка строится вокруг каякинга, переходов между островами, стоянок в тихих бухтах и ощущения полного погружения в природный ритм Карелии. Тур подойдёт путешественникам, которым нужны простор, движение, костёрные вечера и сильное чувство маршрута, а не отдых у моря в классическом понимании.',
                    'duration_days' => 5,
                    'category' => 'adventure',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Скалы и вода Ладоги', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Северные острова', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Каякинг среди шхер', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-15', 'end_date' => '2026-06-19', 'price' => 49900, 'currency' => 'RUB', 'available_seats' => 10],
                    ['start_date' => '2026-07-13', 'end_date' => '2026-07-17', 'price' => 52900, 'currency' => 'RUB', 'available_seats' => 8],
                    ['start_date' => '2026-08-10', 'end_date' => '2026-08-14', 'price' => 54900, 'currency' => 'RUB', 'available_seats' => 8],
                ],
                'route_points' => [
                    ['title' => 'Сортавала', 'description' => 'Старт маршрута, инструктаж и подготовка к выходу на воду.', 'latitude' => 61.7033, 'longitude' => 30.6918, 'sort_order' => 1],
                    ['title' => 'Ладожские шхеры', 'description' => 'Переходы на каяках между островами, скалами и защищёнными бухтами.', 'latitude' => 61.5570, 'longitude' => 30.3126, 'sort_order' => 2],
                    ['title' => 'Остров стоянки', 'description' => 'Ночь в природной локации с костром, водой и полной тишиной вокруг.', 'latitude' => 61.5854, 'longitude' => 30.4208, 'sort_order' => 3],
                    ['title' => 'Видовая скала', 'description' => 'Финальный подъём ради панорамы шхер и водной мозаики Ладоги.', 'latitude' => 61.6207, 'longitude' => 30.5004, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Термальный ретрит в Тюмени',
                    'slug' => 'termalnyi-retrit-v-tyumeni',
                    'short_description' => 'Горячие источники, спокойный ритм и восстановление в термальном формате.',
                    'description' => 'Тур в Тюмень собран как мягкая поездка на восстановление: с термальными бассейнами, неспешными прогулками и ощущением тёплого отдыха даже вне летнего сезона. Это хороший вариант для тех, кто ищет расслабление и перезагрузку без курортной суеты и без насыщенной экскурсионной части. Главный акцент здесь на воде, тепле и физическом комфорте, а не на выездах к береговой линии.',
                    'duration_days' => 3,
                    'category' => 'nature',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Тёплый бассейн под открытым небом', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Тихая зона отдыха', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Расслабленный wellness-формат', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-02-06', 'end_date' => '2026-02-08', 'price' => 28900, 'currency' => 'RUB', 'available_seats' => 18],
                    ['start_date' => '2026-10-16', 'end_date' => '2026-10-18', 'price' => 29900, 'currency' => 'RUB', 'available_seats' => 18],
                    ['start_date' => '2026-11-20', 'end_date' => '2026-11-22', 'price' => 30900, 'currency' => 'RUB', 'available_seats' => 16],
                ],
                'route_points' => [
                    ['title' => 'Тюмень', 'description' => 'Короткий городской старт и переход в формат спокойного отдыха.', 'latitude' => 57.1530, 'longitude' => 65.5343, 'sort_order' => 1],
                    ['title' => 'Термальный комплекс', 'description' => 'Главная часть программы: горячие источники и восстановление в воде.', 'latitude' => 57.2118, 'longitude' => 65.6204, 'sort_order' => 2],
                    ['title' => 'Парк у Туры', 'description' => 'Неспешная прогулка в зелёной зоне в свободном ритме без перегруза активностями.', 'latitude' => 57.1608, 'longitude' => 65.5351, 'sort_order' => 3],
                    ['title' => 'Spa-зона', 'description' => 'Финальный акцент на отдых, тепле и ощущении перезагрузки.', 'latitude' => 57.2051, 'longitude' => 65.6090, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Белое море и северный ветер',
                    'slug' => 'beloe-more-i-severnyi-veter',
                    'short_description' => 'Холодное побережье, приливы, северные посёлки и суровая морская атмосфера.',
                    'description' => 'Этот северный маршрут подходит для тех, кому интересно море не как пляж и отпуск, а как сильный природный опыт. Белое море здесь раскрывается через приливы, каменистое побережье, прохладный ветер, длинный горизонт и спокойный ритм северных поселений. Поездка специально не про купание и курорт, а про фактуру Севера, наблюдение за морем и медленное погружение в прибрежный ландшафт.',
                    'duration_days' => 4,
                    'category' => 'winter',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1483347756197-71ef80e95f73?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1547448415-e9f5b28e570d?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Северное побережье', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Холодный морской берег', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1515238152791-8216bfdf89a7?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Северный пейзаж у воды', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-03-05', 'end_date' => '2026-03-08', 'price' => 41900, 'currency' => 'RUB', 'available_seats' => 12],
                    ['start_date' => '2026-09-24', 'end_date' => '2026-09-27', 'price' => 43900, 'currency' => 'RUB', 'available_seats' => 10],
                    ['start_date' => '2026-10-22', 'end_date' => '2026-10-25', 'price' => 44900, 'currency' => 'RUB', 'available_seats' => 10],
                ],
                'route_points' => [
                    ['title' => 'Кемь', 'description' => 'Точка входа в северный маршрут и первое знакомство с побережьем.', 'latitude' => 64.9555, 'longitude' => 34.5793, 'sort_order' => 1],
                    ['title' => 'Беломорское побережье', 'description' => 'Приливы, ветер, камни и главное ощущение сурового моря.', 'latitude' => 64.9768, 'longitude' => 34.7621, 'sort_order' => 2],
                    ['title' => 'Рабочеостровск', 'description' => 'Посёлок с сильной северной атмосферой и неторопливым ритмом.', 'latitude' => 64.9854, 'longitude' => 34.7605, 'sort_order' => 3],
                    ['title' => 'Смотровая точка', 'description' => 'Финальный выход к открытому горизонту и холодному морскому ветру.', 'latitude' => 65.0044, 'longitude' => 34.8240, 'sort_order' => 4],
                ],
            ],
            [
                'tour' => [
                    'title' => 'Казанский гастро-уикенд',
                    'slug' => 'kazanskii-gastro-uikend',
                    'short_description' => 'Локальная кухня, исторический центр и насыщенные вкусы без морской темы.',
                    'description' => 'Этот маршрут в Казани собран вокруг гастрономии, городской атмосферы и культурного контекста. За короткую поездку гости проходят через рынки, современные рестораны, татарские специалитеты и знаковые городские пространства, не уезжая в природный или пляжный формат. Тур подойдёт тем, кто ищет вкусный, насыщенный и при этом компактный уикенд с сильным локальным характером.',
                    'duration_days' => 3,
                    'category' => 'gastro',
                    'is_active' => true,
                    'main_image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80',
                ],
                'images' => [
                    ['image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Казанский ресторан', 'sort_order' => 1],
                    ['image_url' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Локальная гастрономия', 'sort_order' => 2],
                    ['image_url' => 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1200&q=80', 'alt_text' => 'Исторический центр Казани', 'sort_order' => 3],
                ],
                'dates' => [
                    ['start_date' => '2026-06-05', 'end_date' => '2026-06-07', 'price' => 31900, 'currency' => 'RUB', 'available_seats' => 18],
                    ['start_date' => '2026-09-04', 'end_date' => '2026-09-06', 'price' => 33900, 'currency' => 'RUB', 'available_seats' => 16],
                    ['start_date' => '2026-10-09', 'end_date' => '2026-10-11', 'price' => 34900, 'currency' => 'RUB', 'available_seats' => 16],
                ],
                'route_points' => [
                    ['title' => 'Казанский кремль', 'description' => 'Старт прогулки через главный исторический центр города.', 'latitude' => 55.7985, 'longitude' => 49.1064, 'sort_order' => 1],
                    ['title' => 'Центральный рынок', 'description' => 'Знакомство с локальными вкусами и продуктами региона.', 'latitude' => 55.7887, 'longitude' => 49.1221, 'sort_order' => 2],
                    ['title' => 'Старо-Татарская слобода', 'description' => 'Городской и культурный контекст, который дополняет гастрономическую часть.', 'latitude' => 55.7797, 'longitude' => 49.1147, 'sort_order' => 3],
                    ['title' => 'Авторский ужин', 'description' => 'Финальный гастрономический акцент маршрута без ухода в экскурсионную гонку.', 'latitude' => 55.7902, 'longitude' => 49.1204, 'sort_order' => 4],
                ],
            ],
        ];

        /** @var TourService $tourService */
        $tourService = app(TourService::class);

        foreach ($tours as $payload) {
            $existingTour = Tour::query()
                ->where('slug', $payload['tour']['slug'])
                ->first();

            /** @var Tour $tour */
            $tour = $existingTour !== null
                ? $tourService->update($existingTour, $payload['tour'])
                : $tourService->create($payload['tour']);

            $tour->images()->delete();
            $tour->dates()->delete();
            $tour->routePoints()->delete();

            $tour->images()->createMany($payload['images']);
            $tour->dates()->createMany($payload['dates']);
            $tour->routePoints()->createMany($payload['route_points']);

            $tourService->refreshEmbedding($tour);
        }
    }
}
