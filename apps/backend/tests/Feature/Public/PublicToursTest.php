<?php

namespace Tests\Feature\Public;

use App\Exceptions\EmbeddingServiceUnavailableException;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\TourImage;
use App\Models\TourRoutePoint;
use App\Services\SemanticSearchService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PublicToursTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_active_tours_list(): void
    {
        $activeTour = Tour::factory()->create(['is_active' => true]);
        TourDate::factory()->create(['tour_id' => $activeTour->id, 'price' => 35000]);

        $response = $this->getJson('/api/tours');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $activeTour->id,
                'slug' => $activeTour->slug,
            ]);
    }

    public function test_inactive_tours_do_not_appear_in_public_list(): void
    {
        $activeTour = Tour::factory()->create(['is_active' => true]);
        $inactiveTour = Tour::factory()->inactive()->create();
        TourDate::factory()->create(['tour_id' => $activeTour->id]);
        TourDate::factory()->create(['tour_id' => $inactiveTour->id]);

        $response = $this->getJson('/api/tours');

        $response->assertOk();
        $this->assertEquals([$activeTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_can_get_tour_by_slug(): void
    {
        $tour = Tour::factory()->create([
            'slug' => 'my-amazing-tour',
            'is_active' => true,
        ]);

        TourImage::factory()->count(2)->create(['tour_id' => $tour->id]);
        TourDate::factory()->count(2)->create(['tour_id' => $tour->id]);
        TourRoutePoint::factory()->count(3)->create(['tour_id' => $tour->id]);

        $response = $this->getJson("/api/tours/{$tour->slug}");

        $response
            ->assertOk()
            ->assertJsonPath('data.slug', 'my-amazing-tour');

        $this->assertCount(2, $response->json('data.images'));
        $this->assertCount(2, $response->json('data.dates'));
        $this->assertCount(3, $response->json('data.route_points'));
    }

    public function test_category_filter_works(): void
    {
        $hikingTour = Tour::factory()->create(['category' => 'hiking']);
        $cityTour = Tour::factory()->create(['category' => 'city']);
        TourDate::factory()->create(['tour_id' => $hikingTour->id]);
        TourDate::factory()->create(['tour_id' => $cityTour->id]);

        $response = $this->getJson('/api/tours?category=hiking');

        $response->assertOk();
        $this->assertEquals([$hikingTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_duration_filter_works(): void
    {
        $shortTour = Tour::factory()->create(['duration_days' => 3]);
        $longTour = Tour::factory()->create(['duration_days' => 8]);
        TourDate::factory()->create(['tour_id' => $shortTour->id]);
        TourDate::factory()->create(['tour_id' => $longTour->id]);

        $response = $this->getJson('/api/tours?duration_min=5&duration_max=9');

        $response->assertOk();
        $this->assertEquals([$longTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_price_filter_works(): void
    {
        $budgetTour = Tour::factory()->create();
        $premiumTour = Tour::factory()->create();
        TourDate::factory()->create(['tour_id' => $budgetTour->id, 'price' => 25000]);
        TourDate::factory()->create(['tour_id' => $premiumTour->id, 'price' => 95000]);

        $response = $this->getJson('/api/tours?price_min=20000&price_max=30000');

        $response->assertOk();
        $this->assertEquals([$budgetTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_text_search_works(): void
    {
        $matchingTour = Tour::factory()->create([
            'title' => 'Arctic Northern Lights',
            'short_description' => 'Cold weather experience',
        ]);
        $otherTour = Tour::factory()->create([
            'title' => 'Sunny beach tour',
        ]);
        TourDate::factory()->create(['tour_id' => $matchingTour->id]);
        TourDate::factory()->create(['tour_id' => $otherTour->id]);

        $response = $this->getJson('/api/tours?search=northern');

        $response->assertOk();
        $this->assertEquals([$matchingTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_catalog_search_combines_text_and_semantic_results(): void
    {
        $lexicalTour = Tour::factory()->create([
            'title' => 'Пляжный отдых на Каспийском море',
            'short_description' => 'Песчаный берег и купание.',
            'description' => 'Спокойный отпуск у воды с пляжем и тёплым морем.',
        ]);
        $semanticTour = Tour::factory()->create([
            'title' => 'Курортные выходные в Сочи',
            'short_description' => 'Южный релакс и размеренный отдых.',
            'description' => 'Тёплый курортный формат с прогулками и восстановлением сил.',
        ]);
        $otherTour = Tour::factory()->create([
            'title' => 'Горный трек по Алтаю',
            'short_description' => 'Перевалы и каменные тропы.',
        ]);

        TourDate::factory()->create(['tour_id' => $lexicalTour->id]);
        TourDate::factory()->create(['tour_id' => $semanticTour->id]);
        TourDate::factory()->create(['tour_id' => $otherTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock) use ($lexicalTour, $semanticTour): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('отдых у моря', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection([
                    tap($lexicalTour, fn (Tour $tour) => $tour->setAttribute('score', 0.76)),
                    tap($semanticTour, fn (Tour $tour) => $tour->setAttribute('score', 0.84)),
                ]));
        });

        $response = $this->getJson('/api/tours?search=отдых у моря');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $lexicalTour->id)
            ->assertJsonPath('data.1.id', $semanticTour->id)
            ->assertJsonMissing([
                'id' => $otherTour->id,
                'slug' => $otherTour->slug,
            ]);
    }

    public function test_catalog_search_applies_filters_before_semantic_ranking(): void
    {
        $natureTour = Tour::factory()->create([
            'title' => 'Морской уикенд',
            'category' => 'nature',
        ]);
        $cityTour = Tour::factory()->create([
            'title' => 'Морской городской маршрут',
            'category' => 'city',
        ]);

        TourDate::factory()->create(['tour_id' => $natureTour->id]);
        TourDate::factory()->create(['tour_id' => $cityTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock) use ($natureTour): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('море', Mockery::on(function ($tours) use ($natureTour): bool {
                    return $tours instanceof EloquentCollection
                        && $tours->pluck('id')->all() === [$natureTour->id];
                }))
                ->andReturn(new EloquentCollection([
                    tap($natureTour, fn (Tour $tour) => $tour->setAttribute('score', 0.79)),
                ]));
        });

        $response = $this->getJson('/api/tours?search=море&category=nature');

        $response->assertOk();
        $this->assertEquals([$natureTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_catalog_search_falls_back_to_text_when_semantic_service_is_unavailable(): void
    {
        $matchingTour = Tour::factory()->create([
            'title' => 'Arctic Northern Lights',
            'short_description' => 'Cold weather experience',
        ]);
        $otherTour = Tour::factory()->create([
            'title' => 'Sunny beach tour',
        ]);
        TourDate::factory()->create(['tour_id' => $matchingTour->id]);
        TourDate::factory()->create(['tour_id' => $otherTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('northern', Mockery::type(EloquentCollection::class))
                ->andThrow(new EmbeddingServiceUnavailableException('Semantic search временно недоступен.'));
        });

        $response = $this->getJson('/api/tours?search=northern');

        $response->assertOk();
        $this->assertEquals([$matchingTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_catalog_search_paginates_ranked_results(): void
    {
        $matchingTours = collect(range(1, 5))->map(function (int $index): Tour {
            $tour = Tour::factory()->create([
                'title' => "Морской тур {$index}",
                'created_at' => now()->subMinutes($index),
                'updated_at' => now()->subMinutes($index),
            ]);

            TourDate::factory()->create(['tour_id' => $tour->id]);

            return $tour;
        });

        $this->mock(SemanticSearchService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('морской', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection());
        });

        $response = $this->getJson('/api/tours?search=морской&page=2&per_page=2');

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', $matchingTours->count());

        $this->assertCount(2, $response->json('data'));
    }

    public function test_catalog_search_does_not_return_weak_partial_text_matches_without_semantic_support(): void
    {
        $strongMatch = Tour::factory()->create([
            'title' => 'Пляжный отдых на Каспийском море',
            'short_description' => 'Спокойный отпуск у воды.',
            'category' => 'nature',
        ]);
        $weakMatch = Tour::factory()->create([
            'title' => 'Белое море и северный ветер',
            'short_description' => 'Суровое побережье без курортного отдыха.',
            'category' => 'winter',
        ]);

        TourDate::factory()->create(['tour_id' => $strongMatch->id]);
        TourDate::factory()->create(['tour_id' => $weakMatch->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('отдых у моря', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection());
        });

        $response = $this->getJson('/api/tours?search=отдых у моря');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $strongMatch->id)
            ->assertJsonMissing([
                'id' => $weakMatch->id,
                'slug' => $weakMatch->slug,
            ]);
    }

    public function test_catalog_search_excludes_non_relax_categories_for_lexical_only_seaside_query(): void
    {
        $allowedTour = Tour::factory()->create([
            'title' => 'Отдых у моря в Светлогорске',
            'short_description' => 'Спокойный формат у моря и побережья.',
            'category' => 'nature',
        ]);
        $blockedTour = Tour::factory()->create([
            'title' => 'Отдых у моря в Калининграде',
            'short_description' => 'Гастрономический маршрут у моря.',
            'category' => 'gastro',
        ]);

        TourDate::factory()->create(['tour_id' => $allowedTour->id]);
        TourDate::factory()->create(['tour_id' => $blockedTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('отдых у моря', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection());
        });

        $response = $this->getJson('/api/tours?search=отдых у моря');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $allowedTour->id)
            ->assertJsonMissing([
                'id' => $blockedTour->id,
                'slug' => $blockedTour->slug,
            ]);
    }

    public function test_catalog_search_specific_query_removes_semantic_tail_noise(): void
    {
        $exactMatch = Tour::factory()->create([
            'title' => 'Казанский гастро-уикенд',
            'category' => 'gastro',
        ]);
        $semanticNoiseOne = Tour::factory()->create([
            'title' => 'Гастрономический тур по Калининграду',
            'category' => 'gastro',
        ]);
        $semanticNoiseTwo = Tour::factory()->create([
            'title' => 'Экспедиция на Камчатку',
            'category' => 'adventure',
        ]);

        TourDate::factory()->create(['tour_id' => $exactMatch->id]);
        TourDate::factory()->create(['tour_id' => $semanticNoiseOne->id]);
        TourDate::factory()->create(['tour_id' => $semanticNoiseTwo->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock) use ($exactMatch, $semanticNoiseOne, $semanticNoiseTwo): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('казанский', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection([
                    tap($exactMatch, fn (Tour $tour) => $tour->setAttribute('score', 0.81)),
                    tap($semanticNoiseOne, fn (Tour $tour) => $tour->setAttribute('score', 0.74)),
                    tap($semanticNoiseTwo, fn (Tour $tour) => $tour->setAttribute('score', 0.71)),
                ]));
        });

        $response = $this->getJson('/api/tours?search=казанский');

        $response->assertOk();
        $this->assertEquals([$exactMatch->id], array_column($response->json('data'), 'id'));
    }

    public function test_catalog_search_food_alias_matches_gastro_tours(): void
    {
        $kaliningradGastro = Tour::factory()->create([
            'title' => 'Гастрономический тур по Калининграду',
            'category' => 'gastro',
        ]);
        $kazanGastro = Tour::factory()->create([
            'title' => 'Казанский гастро-уикенд',
            'category' => 'gastro',
        ]);
        $cityTour = Tour::factory()->create([
            'title' => 'Городской уикенд в Санкт-Петербурге',
            'category' => 'city',
        ]);

        TourDate::factory()->create(['tour_id' => $kaliningradGastro->id]);
        TourDate::factory()->create(['tour_id' => $kazanGastro->id]);
        TourDate::factory()->create(['tour_id' => $cityTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('еда', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection());
        });

        $response = $this->getJson('/api/tours?search=еда');

        $response->assertOk();
        $this->assertEqualsCanonicalizing(
            [$kaliningradGastro->id, $kazanGastro->id],
            array_column($response->json('data'), 'id'),
        );
    }

    public function test_catalog_search_city_query_excludes_semantic_hiking_noise(): void
    {
        $cityTour = Tour::factory()->create([
            'title' => 'Городской уикенд в Санкт-Петербурге',
            'category' => 'city',
        ]);
        $hikingTour = Tour::factory()->create([
            'title' => 'Треккинг в Приэльбрусье',
            'category' => 'hiking',
        ]);

        TourDate::factory()->create(['tour_id' => $cityTour->id]);
        TourDate::factory()->create(['tour_id' => $hikingTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock) use ($cityTour, $hikingTour): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('городской', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection([
                    tap($cityTour, fn (Tour $tour) => $tour->setAttribute('score', 0.79)),
                    tap($hikingTour, fn (Tour $tour) => $tour->setAttribute('score', 0.74)),
                ]));
        });

        $response = $this->getJson('/api/tours?search=городской');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $cityTour->id)
            ->assertJsonMissing([
                'id' => $hikingTour->id,
                'slug' => $hikingTour->slug,
            ]);
    }

    public function test_catalog_search_city_query_excludes_nature_only_tail(): void
    {
        $cityTour = Tour::factory()->create([
            'title' => 'Городской уикенд в Санкт-Петербурге',
            'category' => 'city',
        ]);
        $natureTour = Tour::factory()->create([
            'title' => 'Балтийский релакс в Светлогорске',
            'short_description' => 'Море, сосны и спокойный отдых.',
            'description' => 'Формат отдыха без суеты большого города и с длинными прогулками у воды.',
            'category' => 'nature',
        ]);

        TourDate::factory()->create(['tour_id' => $cityTour->id]);
        TourDate::factory()->create(['tour_id' => $natureTour->id]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock) use ($cityTour): void {
            $mock
                ->shouldReceive('scoreCandidates')
                ->once()
                ->with('городской', Mockery::type(EloquentCollection::class))
                ->andReturn(new EloquentCollection([
                    tap($cityTour, fn (Tour $tour) => $tour->setAttribute('score', 0.8)),
                ]));
        });

        $response = $this->getJson('/api/tours?search=городской');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $cityTour->id)
            ->assertJsonMissing([
                'id' => $natureTour->id,
                'slug' => $natureTour->slug,
            ]);
    }

    public function test_newest_sort_works(): void
    {
        $olderTour = Tour::factory()->create([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $newerTour = Tour::factory()->create([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        TourDate::factory()->create(['tour_id' => $olderTour->id]);
        TourDate::factory()->create(['tour_id' => $newerTour->id]);

        $response = $this->getJson('/api/tours?sort=newest');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $newerTour->id)
            ->assertJsonPath('data.1.id', $olderTour->id);
    }

    public function test_pagination_works(): void
    {
        Tour::factory()->count(15)->create()->each(function (Tour $tour): void {
            TourDate::factory()->create(['tour_id' => $tour->id]);
        });

        $response = $this->getJson('/api/tours?page=2&per_page=5');

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_default_per_page_is_twelve(): void
    {
        Tour::factory()->count(15)->create()->each(function (Tour $tour): void {
            TourDate::factory()->create(['tour_id' => $tour->id]);
        });

        $response = $this->getJson('/api/tours');

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 12);

        $this->assertCount(12, $response->json('data'));
    }

    public function test_per_page_is_capped_at_fifty(): void
    {
        Tour::factory()->count(60)->create()->each(function (Tour $tour): void {
            TourDate::factory()->create(['tour_id' => $tour->id]);
        });

        $response = $this->getJson('/api/tours?per_page=100');

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 50);

        $this->assertCount(50, $response->json('data'));
    }

    public function test_price_ascending_sort_works(): void
    {
        $cheapTour = Tour::factory()->create();
        $midTour = Tour::factory()->create();
        $expensiveTour = Tour::factory()->create();

        TourDate::factory()->create(['tour_id' => $cheapTour->id, 'price' => 20000]);
        TourDate::factory()->create(['tour_id' => $midTour->id, 'price' => 50000]);
        TourDate::factory()->create(['tour_id' => $expensiveTour->id, 'price' => 90000]);

        $response = $this->getJson('/api/tours?sort=price_asc');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $cheapTour->id)
            ->assertJsonPath('data.1.id', $midTour->id)
            ->assertJsonPath('data.2.id', $expensiveTour->id);
    }

    public function test_price_descending_sort_works(): void
    {
        $cheapTour = Tour::factory()->create();
        $midTour = Tour::factory()->create();
        $expensiveTour = Tour::factory()->create();

        TourDate::factory()->create(['tour_id' => $cheapTour->id, 'price' => 20000]);
        TourDate::factory()->create(['tour_id' => $midTour->id, 'price' => 50000]);
        TourDate::factory()->create(['tour_id' => $expensiveTour->id, 'price' => 90000]);

        $response = $this->getJson('/api/tours?sort=price_desc');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $expensiveTour->id)
            ->assertJsonPath('data.1.id', $midTour->id)
            ->assertJsonPath('data.2.id', $cheapTour->id);
    }

    public function test_duration_ascending_sort_works(): void
    {
        $shortTour = Tour::factory()->create(['duration_days' => 3]);
        $mediumTour = Tour::factory()->create(['duration_days' => 5]);
        $longTour = Tour::factory()->create(['duration_days' => 8]);

        TourDate::factory()->create(['tour_id' => $shortTour->id]);
        TourDate::factory()->create(['tour_id' => $mediumTour->id]);
        TourDate::factory()->create(['tour_id' => $longTour->id]);

        $response = $this->getJson('/api/tours?sort=duration_asc');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $shortTour->id)
            ->assertJsonPath('data.1.id', $mediumTour->id)
            ->assertJsonPath('data.2.id', $longTour->id);
    }

    public function test_duration_descending_sort_works(): void
    {
        $shortTour = Tour::factory()->create(['duration_days' => 3]);
        $mediumTour = Tour::factory()->create(['duration_days' => 5]);
        $longTour = Tour::factory()->create(['duration_days' => 8]);

        TourDate::factory()->create(['tour_id' => $shortTour->id]);
        TourDate::factory()->create(['tour_id' => $mediumTour->id]);
        TourDate::factory()->create(['tour_id' => $longTour->id]);

        $response = $this->getJson('/api/tours?sort=duration_desc');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $longTour->id)
            ->assertJsonPath('data.1.id', $mediumTour->id)
            ->assertJsonPath('data.2.id', $shortTour->id);
    }

    public function test_text_search_works_with_cyrillic_query(): void
    {
        $matchingTour = Tour::factory()->create([
            'title' => 'Тур по Байкалу',
            'short_description' => 'Озеро и скалы',
        ]);
        $otherTour = Tour::factory()->create([
            'title' => 'Тур по Алтаю',
        ]);
        TourDate::factory()->create(['tour_id' => $matchingTour->id]);
        TourDate::factory()->create(['tour_id' => $otherTour->id]);

        $response = $this->getJson('/api/tours?search=байкал');

        $response->assertOk();
        $this->assertEquals([$matchingTour->id], array_column($response->json('data'), 'id'));
    }

    public function test_inactive_tour_slug_is_not_accessible_publicly(): void
    {
        $tour = Tour::factory()->inactive()->create([
            'slug' => 'private-tour',
        ]);
        TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->getJson('/api/tours/private-tour')->assertNotFound();
    }

    public function test_public_tour_route_points_are_sorted_by_sort_order(): void
    {
        $tour = Tour::factory()->create([
            'slug' => 'sorted-route-tour',
            'is_active' => true,
        ]);

        TourDate::factory()->create(['tour_id' => $tour->id]);
        TourRoutePoint::factory()->create([
            'tour_id' => $tour->id,
            'title' => 'Third point',
            'sort_order' => 3,
        ]);
        TourRoutePoint::factory()->create([
            'tour_id' => $tour->id,
            'title' => 'First point',
            'sort_order' => 1,
        ]);
        TourRoutePoint::factory()->create([
            'tour_id' => $tour->id,
            'title' => 'Second point',
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/tours/sorted-route-tour');

        $response->assertOk();

        $this->assertSame([1, 2, 3], array_column($response->json('data.route_points'), 'sort_order'));
    }
}
