<?php

namespace App\Services;

use App\Models\Tour;
use BackedEnum;
use Illuminate\Database\Eloquent\Collection;

class SemanticSearchService
{
    /**
     * @var array<string, list<string>>
     */
    private const INTENT_KEYWORDS = [
        'sea' => ['мор', 'океан', 'касп', 'балтий', 'черномор', 'баренц', 'coast', 'sea', 'ocean'],
        'beach' => ['пляж', 'побереж', 'набереж', 'shore', 'beach', 'coast', 'promenade'],
        'relax' => ['отдых', 'релакс', 'спокой', 'отпуск', 'курорт', 'восстанов', 'relax', 'vacation', 'resort', 'wellness'],
        'warm' => ['тепл', 'солн', 'летн', 'южн', 'купан', 'sun', 'warm', 'summer', 'swim'],
        'winter' => ['зим', 'снег', 'лед', 'аркти', 'север', 'cold', 'winter', 'snow', 'ice'],
    ];

    public function __construct(
        private readonly EmbeddingService $embeddingService,
    ) {
    }

    /**
     * @return Collection<int, Tour>
     */
    public function search(string $query, int $limit = 10): Collection
    {
        $matches = $this->scoreCandidates(
            $query,
            Tour::query()
            ->active()
            ->with(['images', 'dates', 'embedding', 'routePoints'])
            ->withMin('dates', 'price')
            ->get()
        );

        $matches = $matches->take($limit)->values();

        return new Collection($matches->all());
    }

    /**
     * @param  Collection<int, Tour>  $tours
     * @return Collection<int, Tour>
     */
    public function scoreCandidates(string $query, Collection $tours, ?float $threshold = null): Collection
    {
        $queryEmbedding = $this->embeddingService->generateForQuery($query);
        $queryConcepts = $this->detectConcepts($query);
        $minimumScore = $threshold ?? $this->threshold();

        $matches = $tours
            ->map(function (Tour $tour) use ($queryEmbedding, $queryConcepts): array {
                $tourEmbedding = $tour->embedding?->embedding;
                $sourceText = $tour->embedding?->source_text ?? $this->embeddingService->buildSourceText($tour);
                $tourCategory = $tour->category instanceof BackedEnum
                    ? $tour->category->value
                    : (string) $tour->category;

                if (! is_array($tourEmbedding) || $tourEmbedding === []) {
                    $tourEmbedding = $this->embeddingService->generateForTour($tour);
                }

                $score = $this->cosineSimilarity($queryEmbedding, $tourEmbedding);

                if ($score !== null) {
                    $tourConcepts = $this->detectConcepts($sourceText);

                    if (! $this->passesIntentGuards($queryConcepts, $tourConcepts, $tourCategory)) {
                        $score = null;
                    } else {
                        $score = $this->adjustScore($score, $queryConcepts, $tourConcepts);
                    }
                }

                return [
                    'tour' => $tour,
                    'score' => $score,
                ];
            })
            ->filter(fn (array $row): bool => $row['score'] !== null && $row['score'] >= $minimumScore)
            ->sortByDesc('score')
            ->values()
            ->map(function (array $row): Tour {
                $row['tour']->setAttribute('score', round((float) $row['score'], 6));

                return $row['tour'];
            });

        return new Collection($matches->all());
    }

    public function threshold(): float
    {
        return (float) config('services.embedding.semantic_threshold', 0.4);
    }

    /**
     * @param  list<float>  $left
     * @param  array<int, mixed>|null  $right
     */
    private function cosineSimilarity(array $left, ?array $right): ?float
    {
        if ($right === null || $right === [] || count($left) !== count($right)) {
            return null;
        }

        $dotProduct = 0.0;
        $leftNorm = 0.0;
        $rightNorm = 0.0;

        foreach ($left as $index => $leftValue) {
            $rightValue = (float) $right[$index];

            $dotProduct += $leftValue * $rightValue;
            $leftNorm += $leftValue ** 2;
            $rightNorm += $rightValue ** 2;
        }

        if ($leftNorm <= 0.0 || $rightNorm <= 0.0) {
            return null;
        }

        return $dotProduct / (sqrt($leftNorm) * sqrt($rightNorm));
    }

    /**
     * @return array<string, bool>
     */
    private function detectConcepts(string $text): array
    {
        $tokens = $this->tokenize($text);
        $concepts = [];

        foreach (self::INTENT_KEYWORDS as $concept => $keywords) {
            $concepts[$concept] = false;

            foreach ($tokens as $token) {
                foreach ($keywords as $keyword) {
                    if ($this->matchesKeyword($token, $keyword)) {
                        $concepts[$concept] = true;
                        break 2;
                    }
                }
            }
        }

        return $concepts;
    }

    /**
     * @param  array<string, bool>  $queryConcepts
     * @param  array<string, bool>  $tourConcepts
     */
    private function passesIntentGuards(array $queryConcepts, array $tourConcepts, string $tourCategory): bool
    {
        $wantsSeasideRelaxation = ($queryConcepts['sea'] ?? false) && ($queryConcepts['relax'] ?? false);

        if ($wantsSeasideRelaxation) {
            if (! ($tourConcepts['sea'] ?? false)) {
                return false;
            }

            if (! ($tourConcepts['relax'] ?? false)) {
                return false;
            }

            if (! (($tourConcepts['beach'] ?? false) || ($tourConcepts['warm'] ?? false))) {
                return false;
            }

            if ($tourConcepts['winter'] ?? false) {
                return false;
            }

            if (in_array($tourCategory, ['adventure', 'winter', 'hiking', 'gastro'], true)) {
                return false;
            }
        }

        if (($queryConcepts['beach'] ?? false) && ! (($tourConcepts['sea'] ?? false) && ($tourConcepts['beach'] ?? false))) {
            return false;
        }

        if (($queryConcepts['warm'] ?? false) && ! (($tourConcepts['warm'] ?? false) || ($tourConcepts['beach'] ?? false))) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, bool>  $queryConcepts
     * @param  array<string, bool>  $tourConcepts
     */
    private function adjustScore(float $score, array $queryConcepts, array $tourConcepts): float
    {
        $adjusted = $score;

        if ($queryConcepts['sea'] ?? false) {
            if ($tourConcepts['sea'] ?? false) {
                $adjusted += 0.03;
            }

            if ($tourConcepts['beach'] ?? false) {
                $adjusted += 0.04;
            }

            if (($queryConcepts['relax'] ?? false) && ($tourConcepts['relax'] ?? false)) {
                $adjusted += 0.04;
            }

            if (($queryConcepts['relax'] ?? false) && ($tourConcepts['warm'] ?? false)) {
                $adjusted += 0.05;
            }

            if (! ($queryConcepts['winter'] ?? false) && ($tourConcepts['winter'] ?? false)) {
                $adjusted -= 0.12;
            }
        }

        if (($queryConcepts['winter'] ?? false) && ($tourConcepts['winter'] ?? false)) {
            $adjusted += 0.05;
        }

        return max(min($adjusted, 1.0), -1.0);
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
        return $token === $keyword || str_starts_with($token, $keyword);
    }
}
