<?php

namespace App\Services;

use App\Exceptions\EmbeddingServiceUnavailableException;
use App\Models\Tour;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorInstance;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection as SupportCollection;

class TourCatalogQueryService
{
    /**
     * @var array<string, list<string>>
     */
    private const CATEGORY_SEARCH_ALIASES = [
        'gastro' => ['еда', 'гастро', 'гастроном', 'кухн', 'дегустац', 'вкус', 'ресторан', 'food', 'taste'],
        'city' => ['город', 'городск', 'city', 'urban'],
    ];

    public function __construct(
        private readonly SemanticSearchService $semanticSearchService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 12), 1), 50);
        $sort = $filters['sort'] ?? 'newest';
        $search = $this->normalizeSearch($filters['search'] ?? null);

        if ($search === null) {
            $query = $this->baseQuery($filters);
            $this->applySort($query, $sort);

            return $query->paginate($perPage)->withQueryString();
        }

        return $this->paginateSearchResults(
            $this->rankSearchResults($this->baseQuery($filters, includeSemanticRelations: true)->get(), $search, $sort),
            $perPage,
        );
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('dates_min_price')->orderByDesc('created_at'),
            'price_desc' => $query->orderByDesc('dates_min_price')->orderByDesc('created_at'),
            'duration_asc' => $query->orderBy('duration_days')->orderByDesc('created_at'),
            'duration_desc' => $query->orderByDesc('duration_days')->orderByDesc('created_at'),
            default => $query->latest('created_at'),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters, bool $includeSemanticRelations = false): Builder
    {
        $query = Tour::query()
            ->active()
            ->with(['images', 'dates'])
            ->withMin('dates', 'price')
            ->category($filters['category'] ?? null)
            ->durationBetween($filters['duration_min'] ?? null, $filters['duration_max'] ?? null)
            ->priceBetween($filters['price_min'] ?? null, $filters['price_max'] ?? null);

        if ($includeSemanticRelations) {
            $query->with(['embedding', 'routePoints']);
        }

        return $query;
    }

    /**
     * @return SupportCollection<int, Tour>
     */
    private function rankSearchResults(Collection $tours, string $search, string $sort): SupportCollection
    {
        if ($tours->isEmpty()) {
            return collect();
        }

        $queryTokens = $this->tokenize($search);
        $semanticScores = $this->semanticScores($search, $tours);
        $wantsSeasideRelaxation = $this->wantsSeasideRelaxation($queryTokens);
        $wantsCityExperience = $this->wantsCityExperience($queryTokens);

        $rankedRows = $tours
            ->map(function (Tour $tour) use ($search, $semanticScores, $wantsSeasideRelaxation, $wantsCityExperience): array {
                $lexicalScore = $this->lexicalScore($tour, $search);
                $semanticScore = $semanticScores[$tour->id] ?? null;
                $combinedScore = $this->combinedScore($lexicalScore, $semanticScore);

                return [
                    'tour' => $tour,
                    'lexical_score' => $lexicalScore,
                    'semantic_score' => $semanticScore,
                    'allow_lexical_only' => $this->allowsLexicalOnlyMatch($tour, $wantsSeasideRelaxation, $wantsCityExperience),
                    'score' => $combinedScore,
                ];
            });

        $restrictSemanticTail = $this->shouldRestrictSemanticTail($rankedRows, $queryTokens);

        return $rankedRows
            ->filter(fn (array $row): bool => $this->shouldIncludeRankedRow(
                $row['lexical_score'],
                $row['semantic_score'],
                $row['allow_lexical_only'],
                $restrictSemanticTail,
            ))
            ->sort(fn (array $left, array $right): int => $this->compareRankedRows($left, $right, $sort))
            ->values()
            ->map(function (array $row): Tour {
                $row['tour']->setAttribute('score', round($row['score'], 6));

                return $row['tour'];
            });
    }

    /**
     * @return array<int, float>
     */
    private function semanticScores(string $search, Collection $tours): array
    {
        try {
            return $this->semanticSearchService
                ->scoreCandidates($search, $tours)
                ->mapWithKeys(fn (Tour $tour): array => [$tour->id => (float) $tour->score])
                ->all();
        } catch (EmbeddingServiceUnavailableException $exception) {
            report($exception);

            return [];
        }
    }

    private function lexicalScore(Tour $tour, string $search): float
    {
        $normalizedSearch = $this->normalizeText($search);

        if ($normalizedSearch === '') {
            return 0.0;
        }

        $queryTokens = $this->tokenize($search);
        $title = (string) $tour->title;
        $shortDescription = (string) $tour->short_description;
        $description = (string) $tour->description;
        $aliasText = $this->categoryAliasText($tour);

        $score = 0.0;

        if (str_contains($this->normalizeText($title), $normalizedSearch)) {
            $score = max($score, 1.0);
        }

        if (str_contains($this->normalizeText($shortDescription), $normalizedSearch)) {
            $score = max($score, 0.82);
        }

        if (str_contains($this->normalizeText($description), $normalizedSearch)) {
            $score = max($score, 0.68);
        }

        if ($aliasText !== '' && str_contains($this->normalizeText($aliasText), $normalizedSearch)) {
            $score = max($score, 0.76);
        }

        $score = max($score, $this->tokenCoverageScore($queryTokens, $this->tokenize($title), 0.95));
        $score = max($score, $this->tokenCoverageScore($queryTokens, $this->tokenize($shortDescription), 0.74));
        $score = max($score, $this->tokenCoverageScore($queryTokens, $this->tokenize($description), 0.62));
        $score = max($score, $this->tokenCoverageScore($queryTokens, $this->tokenize($aliasText), 0.78));

        return min($score, 1.0);
    }

    private function combinedScore(float $lexicalScore, ?float $semanticScore): float
    {
        if ($lexicalScore <= 0.0 && $semanticScore === null) {
            return 0.0;
        }

        $combined = ($lexicalScore * 0.58) + (($semanticScore ?? 0.0) * 0.62);

        if ($lexicalScore > 0.0 && $semanticScore !== null) {
            $combined += 0.12;
        }

        return min($combined, 1.0);
    }

    private function shouldIncludeRankedRow(
        float $lexicalScore,
        ?float $semanticScore,
        bool $allowLexicalOnly,
        bool $restrictSemanticTail,
    ): bool
    {
        if ($semanticScore !== null) {
            if ($restrictSemanticTail && $lexicalScore <= 0.0) {
                return false;
            }

            return true;
        }

        return $allowLexicalOnly && $lexicalScore >= 0.55;
    }

    /**
     * @param  SupportCollection<int, array{tour: Tour, lexical_score: float, semantic_score: ?float, allow_lexical_only: bool, score: float}>  $rankedRows
     * @param  list<string>  $queryTokens
     */
    private function shouldRestrictSemanticTail(SupportCollection $rankedRows, array $queryTokens): bool
    {
        if ($queryTokens === [] || count($queryTokens) > 2) {
            return false;
        }

        return $rankedRows->contains(
            fn (array $row): bool => $row['lexical_score'] >= 0.75
        );
    }

    /**
     * @param  array{tour: Tour, score: float}  $left
     * @param  array{tour: Tour, score: float}  $right
     */
    private function compareRankedRows(array $left, array $right, string $sort): int
    {
        $scoreComparison = $right['score'] <=> $left['score'];

        if ($scoreComparison !== 0) {
            return $scoreComparison;
        }

        $sortComparison = match ($sort) {
            'price_asc' => $this->nullSafeCompare($left['tour']->dates_min_price, $right['tour']->dates_min_price, false),
            'price_desc' => $this->nullSafeCompare($left['tour']->dates_min_price, $right['tour']->dates_min_price, true),
            'duration_asc' => $left['tour']->duration_days <=> $right['tour']->duration_days,
            'duration_desc' => $right['tour']->duration_days <=> $left['tour']->duration_days,
            default => $this->compareNewest($left['tour'], $right['tour']),
        };

        if ($sortComparison !== 0) {
            return $sortComparison;
        }

        return $right['tour']->id <=> $left['tour']->id;
    }

    private function compareNewest(Tour $left, Tour $right): int
    {
        return $right->created_at?->getTimestamp() <=> $left->created_at?->getTimestamp();
    }

    private function nullSafeCompare(float|int|null $left, float|int|null $right, bool $desc): int
    {
        $leftValue = $left ?? ($desc ? -PHP_FLOAT_MAX : PHP_FLOAT_MAX);
        $rightValue = $right ?? ($desc ? -PHP_FLOAT_MAX : PHP_FLOAT_MAX);

        return $desc
            ? $rightValue <=> $leftValue
            : $leftValue <=> $rightValue;
    }

    /**
     * @param  list<string>  $queryTokens
     * @param  list<string>  $fieldTokens
     */
    private function tokenCoverageScore(array $queryTokens, array $fieldTokens, float $weight): float
    {
        $uniqueQueryTokens = array_values(array_unique($queryTokens));

        if ($uniqueQueryTokens === [] || $fieldTokens === []) {
            return 0.0;
        }

        $matches = 0;

        foreach ($uniqueQueryTokens as $queryToken) {
            foreach ($fieldTokens as $fieldToken) {
                if ($this->tokensMatch($queryToken, $fieldToken)) {
                    $matches++;
                    break;
                }
            }
        }

        return ($matches / count($uniqueQueryTokens)) * $weight;
    }

    private function tokensMatch(string $left, string $right): bool
    {
        if ($left === $right) {
            return true;
        }

        return mb_strlen($left) >= 5
            && mb_strlen($right) >= 5
            && (str_starts_with($left, $right) || str_starts_with($right, $left));
    }

    /**
     * @param  list<string>  $tokens
     */
    private function wantsSeasideRelaxation(array $tokens): bool
    {
        return $this->containsAnyStem($tokens, ['мор', 'океан', 'касп', 'балтий'])
            && $this->containsAnyStem($tokens, ['отдых', 'релакс', 'отпуск', 'курорт']);
    }

    /**
     * @param  list<string>  $tokens
     */
    private function wantsCityExperience(array $tokens): bool
    {
        return $this->containsAnyStem($tokens, ['город', 'городск', 'city', 'urban']);
    }

    /**
     * @param  list<string>  $tokens
     * @param  list<string>  $stems
     */
    private function containsAnyStem(array $tokens, array $stems): bool
    {
        foreach ($tokens as $token) {
            foreach ($stems as $stem) {
                if ($this->tokensMatch($token, $stem)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $normalized = $this->normalizeText($text);
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

    private function normalizeSearch(mixed $search): ?string
    {
        if (! is_string($search)) {
            return null;
        }

        $normalized = trim($search);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeText(string $text): string
    {
        return str_replace('ё', 'е', mb_strtolower(trim($text)));
    }

    private function categoryAliasText(Tour $tour): string
    {
        $category = $this->categoryValue($tour);
        $aliases = self::CATEGORY_SEARCH_ALIASES[$category] ?? [];

        return implode(' ', $aliases);
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

    private function allowsLexicalOnlyMatch(Tour $tour, bool $wantsSeasideRelaxation, bool $wantsCityExperience): bool
    {
        $category = $this->categoryValue($tour);

        if ($wantsSeasideRelaxation && in_array($category, ['adventure', 'winter', 'hiking', 'gastro'], true)) {
            return false;
        }

        if ($wantsCityExperience && in_array($category, ['nature', 'adventure', 'winter', 'hiking'], true)) {
            return false;
        }

        return true;
    }

    private function categoryValue(Tour $tour): string
    {
        return $tour->category?->value ?? (string) $tour->category;
    }

    /**
     * @param  SupportCollection<int, Tour>  $tours
     */
    private function paginateSearchResults(SupportCollection $tours, int $perPage): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();
        $items = $tours->forPage($page, $perPage)->values();

        return (new PaginatorInstance(
            $items,
            $tours->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ],
        ))->withQueryString();
    }
}
