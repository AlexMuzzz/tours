<?php

namespace App\Services;

use App\Models\Tour;
use App\Support\TourMediaPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TourMediaService
{
    public function storeMainImage(Tour $tour, UploadedFile $file): string
    {
        return $file->store("tours/{$tour->id}/main", 'public');
    }

    public function storeGalleryImage(Tour $tour, UploadedFile $file): string
    {
        return $file->store("tours/{$tour->id}/gallery", 'public');
    }

    public function delete(?string $path): void
    {
        if ($path === null || trim($path) === '' || TourMediaPath::isExternalUrl($path)) {
            return;
        }

        Storage::disk('public')->delete(ltrim($path, '/'));
    }

    public function deleteTourDirectory(Tour $tour): void
    {
        Storage::disk('public')->deleteDirectory("tours/{$tour->id}");
    }
}
