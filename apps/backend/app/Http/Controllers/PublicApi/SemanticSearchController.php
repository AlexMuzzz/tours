<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\SemanticSearchRequest;
use App\Http\Resources\TourListResource;
use App\Exceptions\EmbeddingServiceUnavailableException;
use App\Services\SemanticSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SemanticSearchController extends Controller
{
    public function __construct(
        private readonly SemanticSearchService $semanticSearchService,
    ) {
    }

    /**
     * Искать туры через semantic search на embeddings.
     *
     * Для MVP запрос уходит во внешний embedding service, после чего backend сравнивает
     * embedding запроса с embedding-ами туров через cosine similarity и применяет
     * минимальный threshold релевантности.
     */
    public function __invoke(SemanticSearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $results = $this->semanticSearchService->search((string) $request->string('query'));
        } catch (EmbeddingServiceUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        return TourListResource::collection($results)->additional([
            'meta' => [
                'threshold' => (float) config('services.embedding.semantic_threshold', 0.4),
                'count' => $results->count(),
            ],
        ]);
    }
}
