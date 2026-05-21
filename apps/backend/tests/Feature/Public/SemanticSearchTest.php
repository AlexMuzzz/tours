<?php

namespace Tests\Feature\Public;

use App\Exceptions\EmbeddingServiceUnavailableException;
use App\Models\Tour;
use App\Models\TourDate;
use App\Services\EmbeddingService;
use App\Services\SemanticSearchService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_semantic_search_endpoint_works(): void
    {
        $tour = Tour::factory()->create([
            'title' => 'Kamchatka Volcano Adventure',
            'short_description' => 'Volcano trekking and remote landscapes',
        ]);
        TourDate::factory()->create(['tour_id' => $tour->id]);

        $response = $this->getJson('/api/tours/search/semantic?query=volcano');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $tour->id);
    }

    public function test_semantic_search_service_is_invoked(): void
    {
        $tour = Tour::factory()->create([
            'title' => 'Curated search result',
            'slug' => 'curated-search-result',
        ]);

        $this->mock(SemanticSearchService::class, function (MockInterface $mock) use ($tour): void {
            $mock
                ->shouldReceive('search')
                ->once()
                ->with('kamchatka')
                ->andReturn(new EloquentCollection([$tour]));
        });

        $response = $this->getJson('/api/tours/search/semantic?query=kamchatka');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'curated-search-result');
    }

    public function test_empty_semantic_query_returns_validation_error(): void
    {
        $response = $this->getJson('/api/tours/search/semantic?query=');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    public function test_semantic_search_matches_related_meaning_for_russian_query(): void
    {
        $mountainTour = Tour::factory()->create([
            'title' => 'Altai Hiking Escape',
            'short_description' => 'Mountain trails and alpine viewpoints',
            'description' => 'A trekking route with panoramic ridges and highland scenery.',
            'category' => 'hiking',
        ]);
        TourDate::factory()->create(['tour_id' => $mountainTour->id]);

        $lakeTour = Tour::factory()->create([
            'title' => 'Lake Weekend',
            'short_description' => 'Calm shoreline walks and relaxed boat trips',
            'description' => 'A peaceful waterside itinerary with coastal villages and lake viewpoints.',
            'category' => 'nature',
        ]);
        TourDate::factory()->create(['tour_id' => $lakeTour->id]);

        $response = $this->getJson('/api/tours/search/semantic?query=горы');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $mountainTour->id,
                'slug' => $mountainTour->slug,
            ])
            ->assertJsonMissing([
                'id' => $lakeTour->id,
                'slug' => $lakeTour->slug,
            ]);
    }

    public function test_semantic_search_prioritizes_seaside_relaxation_query(): void
    {
        $seasideTour = Tour::factory()->create([
            'title' => 'Каспийский пляжный отдых',
            'short_description' => 'Тёплое море, пляжи и размеренный курортный ритм.',
            'description' => 'Маршрут для тех, кто хочет спокойный отдых у моря, прогулки по побережью и время на купание.',
            'category' => 'nature',
        ]);
        TourDate::factory()->create(['tour_id' => $seasideTour->id]);

        $mountainTour = Tour::factory()->create([
            'title' => 'Горный трек по Алтаю',
            'short_description' => 'Перевалы и альпийские тропы',
            'description' => 'Интенсивный хайкинг по горам с перепадами высот и ежедневными треккинговыми выходами.',
            'category' => 'hiking',
        ]);
        TourDate::factory()->create(['tour_id' => $mountainTour->id]);

        $response = $this->getJson('/api/tours/search/semantic?query=отдых у моря');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $seasideTour->id)
            ->assertJsonMissing([
                'id' => $mountainTour->id,
                'slug' => $mountainTour->slug,
            ]);
    }

    public function test_semantic_search_returns_service_unavailable_when_embedding_service_fails(): void
    {
        $this->mock(SemanticSearchService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('search')
                ->once()
                ->with('отдых у моря')
                ->andThrow(new EmbeddingServiceUnavailableException('Semantic search временно недоступен.'));
        });

        $response = $this->getJson('/api/tours/search/semantic?query=отдых у моря');

        $response
            ->assertStatus(503)
            ->assertJsonPath('message', 'Semantic search временно недоступен.');
    }

    public function test_semantic_search_service_filters_inactive_tours_and_sorts_by_score_desc(): void
    {
        Config::set('services.embedding.semantic_threshold', 0.4);

        $topTour = $this->createTourWithEmbedding(
            [
                'title' => 'Южный пляжный отдых',
                'category' => 'nature',
            ],
            [1.0, 0.0],
            'Тёплое море, пляж, курорт и спокойный отдых у воды.'
        );

        $secondTour = $this->createTourWithEmbedding(
            [
                'title' => 'Курорт на побережье',
                'category' => 'nature',
            ],
            [0.45, 0.89],
            'Побережье, море, прогулки по набережной и размеренный отпуск.'
        );

        $inactiveTour = $this->createTourWithEmbedding(
            [
                'title' => 'Неактивный морской тур',
                'category' => 'nature',
                'is_active' => false,
            ],
            [1.0, 0.0],
            'Тёплое море, пляж и отпуск на побережье.'
        );

        $this->mock(EmbeddingService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('generateForQuery')
                ->once()
                ->with('отдых у моря')
                ->andReturn([1.0, 0.0]);

            $mock->shouldNotReceive('generateForTour');
            $mock->shouldNotReceive('buildSourceText');
        });

        $results = app(SemanticSearchService::class)->search('отдых у моря');

        $this->assertSame([$topTour->id, $secondTour->id], $results->pluck('id')->all());
        $this->assertNotContains($inactiveTour->id, $results->pluck('id')->all());
        $this->assertGreaterThan($results[1]->score, $results[0]->score);
    }

    public function test_semantic_search_service_applies_threshold_to_weak_matches(): void
    {
        Config::set('services.embedding.semantic_threshold', 0.7);

        $tour = $this->createTourWithEmbedding(
            [
                'title' => 'Слабое совпадение',
                'category' => 'city',
            ],
            [0.6, 0.8],
            'Городской уикенд без морского или зимнего контекста.'
        );

        $this->mock(EmbeddingService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('generateForQuery')
                ->once()
                ->with('космос')
                ->andReturn([1.0, 0.0]);

            $mock->shouldNotReceive('generateForTour');
            $mock->shouldNotReceive('buildSourceText');
        });

        $results = app(SemanticSearchService::class)->search('космос');

        $this->assertCount(0, $results);
        $this->assertNotNull($tour->id);
    }

    public function test_semantic_search_service_uses_generated_embedding_when_tour_embedding_is_missing(): void
    {
        Config::set('services.embedding.semantic_threshold', 0.4);

        $tour = Tour::factory()->create([
            'title' => 'Каньоны и панорамы',
            'short_description' => 'Горный маршрут с обзорными площадками.',
            'description' => 'Пешее путешествие с каньонами, смотровыми точками и прогулками.',
            'category' => 'hiking',
        ]);
        TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->mock(EmbeddingService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('generateForQuery')
                ->once()
                ->with('каньон')
                ->andReturn([1.0, 0.0]);

            $mock
                ->shouldReceive('buildSourceText')
                ->once()
                ->andReturn('Каньон, горы и обзорные площадки.');

            $mock
                ->shouldReceive('generateForTour')
                ->once()
                ->andReturn([1.0, 0.0]);
        });

        $results = app(SemanticSearchService::class)->search('каньон');

        $this->assertSame([$tour->id], $results->pluck('id')->all());
    }

    public function test_semantic_search_service_filters_cold_or_inland_results_for_seaside_relaxation_query(): void
    {
        Config::set('services.embedding.semantic_threshold', 0.4);

        $warmSeasideTour = $this->createTourWithEmbedding(
            [
                'title' => 'Тёплый курорт у моря',
                'category' => 'nature',
            ],
            [1.0, 0.0],
            'Тёплое море, пляж, купание, курорт и спокойный отдых у побережья.'
        );

        $coldSeasideTour = $this->createTourWithEmbedding(
            [
                'title' => 'Северный берег Белого моря',
                'category' => 'winter',
            ],
            [1.0, 0.0],
            'Белое море, зимний ветер, северный берег и холодный маршрут.'
        );

        $inlandRelaxTour = $this->createTourWithEmbedding(
            [
                'title' => 'Термальный отдых в городе',
                'category' => 'nature',
            ],
            [1.0, 0.0],
            'Спокойный отдых, термальные бассейны и восстановление в spa-формате.'
        );

        $this->mock(EmbeddingService::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('generateForQuery')
                ->once()
                ->with('отдых у моря')
                ->andReturn([1.0, 0.0]);

            $mock->shouldNotReceive('generateForTour');
            $mock->shouldNotReceive('buildSourceText');
        });

        $results = app(SemanticSearchService::class)->search('отдых у моря');

        $this->assertSame([$warmSeasideTour->id], $results->pluck('id')->all());
        $this->assertNotContains($coldSeasideTour->id, $results->pluck('id')->all());
        $this->assertNotContains($inlandRelaxTour->id, $results->pluck('id')->all());
    }

    private function createTourWithEmbedding(array $tourAttributes, array $embedding, string $sourceText): Tour
    {
        $tour = Tour::factory()->create($tourAttributes);
        TourDate::factory()->create(['tour_id' => $tour->id]);

        $tour->embedding()->create([
            'embedding' => $embedding,
            'source_text' => $sourceText,
        ]);

        return $tour->fresh(['embedding', 'dates']);
    }
}
