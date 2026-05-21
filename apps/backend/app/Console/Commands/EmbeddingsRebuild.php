<?php

namespace App\Console\Commands;

use App\Models\Tour;
use App\Services\TourService;
use Illuminate\Console\Command;

class EmbeddingsRebuild extends Command
{
    protected $signature = 'embeddings:rebuild {--chunk=50 : Number of tours processed per chunk}';

    protected $description = 'Rebuild stored embeddings for all tours.';

    public function __construct(
        private readonly TourService $tourService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = 0;
        $generated = 0;
        $missing = 0;
        $chunkSize = max((int) $this->option('chunk'), 1);

        Tour::query()
            ->with(['routePoints', 'embedding'])
            ->chunkById($chunkSize, function ($tours) use (&$processed, &$generated, &$missing): void {
                foreach ($tours as $tour) {
                    $updatedTour = $this->tourService->refreshEmbedding($tour);

                    $processed++;

                    if (is_array($updatedTour->embedding?->embedding) && $updatedTour->embedding?->embedding !== []) {
                        $generated++;
                    } else {
                        $missing++;
                    }
                }
            });

        $this->info("Processed tours: {$processed}");
        $this->line("Embeddings generated: {$generated}");
        $this->line("Embeddings missing: {$missing}");

        return self::SUCCESS;
    }
}
