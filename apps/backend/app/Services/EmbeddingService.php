<?php

namespace App\Services;

use App\Exceptions\EmbeddingServiceUnavailableException;
use App\Enums\TourCategory;
use App\Models\Tour;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmbeddingService
{
    private const HASH_DIMENSIONS = 32;

    /**
     * @var array<string, list<string>>
     */
    private const CONCEPT_KEYWORDS = [
        'sea' => ['море', 'морск', 'пляж', 'побереж', 'берег', 'океан', 'залив', 'касп', 'балтий', 'shore', 'coast', 'beach', 'sea', 'ocean', 'bay'],
        'relax' => ['отдых', 'релакс', 'спокой', 'курорт', 'закат', 'купан', 'тепл', 'resort', 'relax', 'sunset'],
        'mountain' => ['гор', 'вершин', 'перевал', 'троп', 'трек', 'хребет', 'альп', 'эльбрус', 'алтай', 'mount', 'peak', 'ridge', 'trail', 'hike'],
        'water' => ['озер', 'байкал', 'рек', 'вод', 'lake', 'river', 'waterfall'],
        'culture' => ['культур', 'истор', 'музе', 'монастыр', 'крепост', 'архитектур', 'history', 'museum', 'heritage'],
        'city' => ['город', 'улиц', 'проспект', 'кремл', 'канал', 'набереж', 'city', 'avenue', 'street'],
        'gastro' => ['гастр', 'кухн', 'дегустац', 'вин', 'сыр', 'ресторан', 'еда', 'рыб', 'food', 'wine', 'tasting', 'seafood'],
        'winter' => ['зим', 'снег', 'сиян', 'аркти', 'тундр', 'лед', 'aurora', 'winter', 'snow', 'arctic'],
        'adventure' => ['приключ', 'вулкан', 'экспедиц', 'геотерм', 'остров', 'adventure', 'expedition', 'volcano', 'island'],
        'nature' => ['природ', 'лес', 'пейзаж', 'панорам', 'долин', 'nature', 'forest', 'landscape'],
    ];

    /**
     * @return list<float>|null
     */
    public function generateForTour(Tour $tour): ?array
    {
        return $this->embedText($this->buildSourceText($tour), true);
    }

    /**
     * @return list<float>
     */
    public function generateForQuery(string $query): array
    {
        $embedding = $this->embedText($query, false);

        if ($embedding === null) {
            throw new EmbeddingServiceUnavailableException(
                'Semantic search временно недоступен. Попробуйте повторить запрос позже.'
            );
        }

        return $embedding;
    }

    public function buildSourceText(Tour $tour): string
    {
        $tour->loadMissing('routePoints');
        $category = $tour->category instanceof TourCategory
            ? $tour->category->value.' ('.$tour->category->label().')'
            : (string) $tour->category;

        $routePointText = $tour->routePoints
            ->map(fn ($point): string => trim($point->title.' '.($point->description ?? '')))
            ->filter()
            ->implode(PHP_EOL);

        return trim(implode(PHP_EOL, array_filter([
            $tour->title,
            'Категория: '.$category,
            $tour->short_description,
            $tour->description,
            $routePointText,
        ])));
    }

    /**
     * @return list<float>|null
     */
    private function embedText(string $text, bool $failSilently): ?array
    {
        $serviceUrl = rtrim((string) config('services.embedding.url'), '/');

        if ($serviceUrl === '') {
            Log::warning('Embedding service URL is not configured.');

            return $this->fallbackOrFail(
                $text,
                $failSilently,
                'Semantic search временно недоступен: embedding service не настроен.'
            );
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.embedding.timeout', 20))
                ->post($serviceUrl.'/embed', [
                    'text' => $text,
                ])
                ->throw();

            $embedding = $response->json('embedding');

            if (! is_array($embedding) || $embedding === []) {
                throw new \RuntimeException('Embedding service returned an empty embedding payload.');
            }

            return array_map(static fn ($value): float => (float) $value, $embedding);
        } catch (Throwable $exception) {
            Log::warning('Embedding generation failed.', [
                'message' => $exception->getMessage(),
                'service_url' => $serviceUrl,
            ]);

            return $this->fallbackOrFail(
                $text,
                $failSilently,
                'Semantic search временно недоступен. Не удалось получить embedding для запроса.',
                $exception,
            );
        }
    }

    /**
     * @return list<float>|null
     */
    private function fallbackOrFail(string $text, bool $failSilently, string $message, ?Throwable $previous = null): ?array
    {
        if ((bool) config('services.embedding.fallback_enabled', false)) {
            Log::info('Using local fallback embedding.', [
                'text_length' => mb_strlen($text),
            ]);

            return $this->generateFallbackEmbedding($text);
        }

        if ($failSilently) {
            return null;
        }

        throw new EmbeddingServiceUnavailableException($message, previous: $previous);
    }

    /**
     * @return list<float>
     */
    private function generateFallbackEmbedding(string $text): array
    {
        $tokens = $this->tokenize($text);
        $conceptVector = array_fill(0, count(self::CONCEPT_KEYWORDS), 0.0);
        $hashVector = array_fill(0, self::HASH_DIMENSIONS, 0.0);
        $conceptIndex = array_flip(array_keys(self::CONCEPT_KEYWORDS));

        foreach ($tokens as $token) {
            foreach (self::CONCEPT_KEYWORDS as $concept => $keywords) {
                foreach ($keywords as $keyword) {
                    if ($this->matchesKeyword($token, $keyword)) {
                        $conceptVector[$conceptIndex[$concept]] += 1.0;
                        break;
                    }
                }
            }

            $hashVector[$this->hashIndex($token, self::HASH_DIMENSIONS)] += 0.35;
        }

        /** @var list<float> $vector */
        $vector = [
            ...$conceptVector,
            ...$hashVector,
        ];

        return $this->normalizeVector($vector);
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $normalized = str_replace('ё', 'е', mb_strtolower($text));
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $normalized) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $token = $this->stem($part);

            if (mb_strlen($token) >= 2) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    private function stem(string $token): string
    {
        $suffixes = [
            'иями', 'ями', 'ами', 'его', 'ого', 'ему', 'ому', 'ыми', 'ими',
            'иях', 'ах', 'ях', 'ов', 'ев', 'ий', 'ый', 'ой', 'ая', 'яя', 'ое', 'ее',
            'ые', 'ие', 'ую', 'юю', 'ом', 'ем', 'ам', 'ям', 'а', 'я', 'ы', 'и', 'е', 'о', 'у', 'ю',
            'ing', 'ers', 'ies', 'er', 'ed', 'es', 's',
        ];

        foreach ($suffixes as $suffix) {
            if (mb_strlen($token) <= mb_strlen($suffix) + 2) {
                continue;
            }

            if (mb_substr($token, -mb_strlen($suffix)) === $suffix) {
                return mb_substr($token, 0, mb_strlen($token) - mb_strlen($suffix));
            }
        }

        return $token;
    }

    private function matchesKeyword(string $token, string $keyword): bool
    {
        return $token === $keyword || str_starts_with($token, $keyword) || str_starts_with($keyword, $token);
    }

    private function hashIndex(string $token, int $dimensions): int
    {
        return (int) sprintf('%u', crc32($token)) % $dimensions;
    }

    /**
     * @param  list<float>  $vector
     * @return list<float>
     */
    private function normalizeVector(array $vector): array
    {
        $norm = sqrt(array_sum(array_map(static fn (float $value): float => $value ** 2, $vector)));

        if ($norm <= 0.0) {
            return $vector;
        }

        return array_map(static fn (float $value): float => round($value / $norm, 6), $vector);
    }
}
