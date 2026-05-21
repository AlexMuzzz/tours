<?php

namespace App\Models;

use App\Enums\TourCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'duration_days',
        'category',
        'is_active',
        'main_image',
    ];

    protected function casts(): array
    {
        return [
            'category' => TourCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class)->orderBy('sort_order');
    }

    public function dates(): HasMany
    {
        return $this->hasMany(TourDate::class)->orderBy('start_date');
    }

    public function routePoints(): HasMany
    {
        return $this->hasMany(TourRoutePoint::class)->orderBy('sort_order');
    }

    public function embedding(): HasOne
    {
        return $this->hasOne(TourEmbedding::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory(Builder $query, TourCategory|string|null $category): Builder
    {
        if ($category === null || $category === '') {
            return $query;
        }

        $value = $category instanceof TourCategory ? $category->value : $category;

        return $query->where('category', $value);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = mb_strtolower(trim($search));

        return $query->where(function (Builder $builder) use ($term): void {
            $like = '%'.$term.'%';

            $builder
                ->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(short_description) LIKE ?', [$like])
                ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
        });
    }

    public function scopeDurationBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        return $query
            ->when($min !== null, fn (Builder $builder) => $builder->where('duration_days', '>=', $min))
            ->when($max !== null, fn (Builder $builder) => $builder->where('duration_days', '<=', $max));
    }

    public function scopePriceBetween(Builder $query, float|int|null $min, float|int|null $max): Builder
    {
        if ($min === null && $max === null) {
            return $query;
        }

        return $query->whereHas('dates', function (Builder $builder) use ($min, $max): void {
            $builder
                ->when($min !== null, fn (Builder $dateQuery) => $dateQuery->where('price', '>=', $min))
                ->when($max !== null, fn (Builder $dateQuery) => $dateQuery->where('price', '<=', $max));
        });
    }
}
