<?php

declare(strict_types=1);

namespace Modules\Listing\Support;

use Illuminate\Support\Collection;
use Modules\Category\Models\Category;

final class SampleListingImageCatalog
{
    private const DIRECTORY = 'sample_image';

    private const MAX_PIXELS = 12000000;

    private const MAX_EDGE = 4200;

    private const JOB_IMAGES = [
        'jobs.jpg',
        'business white career hiring recruitment academic jobs.jpg',
        'office business people laptop work team classroom grey teamwork table.jpg',
        'office business technology meeting coding grey engineering engineer software engineer professional woman whiteboard tutor.jpg',
        'office business work team white customer service studio office building.jpg',
        'vintage red text retro machine sign blur bokeh flag hiring.jpg',
    ];

    public static function uniquePaths(): Collection
    {
        return self::pathsForFileNames(self::JOB_IMAGES)
            ->sortBy(fn (string $path): string => strtolower((string) basename($path)))
            ->map(fn (string $path): array => [
                'path' => $path,
                'hash' => md5_file($path) ?: strtolower((string) basename($path)),
            ])
            ->unique('hash')
            ->pluck('path')
            ->values();
    }

    public static function pathFor(Category $category, int $seed): ?string
    {
        $paths = self::resolvePathsForCategory($category);

        if ($paths->isEmpty()) {
            $paths = self::uniquePaths();
        }

        if ($paths->isEmpty()) {
            return null;
        }

        return $paths->get(abs($seed) % $paths->count());
    }

    public static function fileNameFor(string $absolutePath, string $slug): string
    {
        $extension = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        $hash = md5_file($absolutePath);
        $hashSuffix = is_string($hash) && $hash !== ''
            ? '-'.substr($hash, 0, 8)
            : '';

        return $slug.$hashSuffix.($extension !== '' ? '.'.$extension : '');
    }

    private static function resolvePathsForSlug(string $slug): Collection
    {
        return self::pathsForFileNames(self::JOB_IMAGES);
    }

    private static function resolvePathsForCategory(Category $category): Collection
    {
        $categoryPaths = self::resolvePathsForSlug((string) $category->slug);

        if ($categoryPaths->isNotEmpty()) {
            return $categoryPaths;
        }

        $parentSlug = (string) ($category->parent?->slug ?? '');

        return $parentSlug !== ''
            ? self::resolvePathsForSlug($parentSlug)
            : collect();
    }

    private static function pathsForFileNames(array $fileNames): Collection
    {
        return collect($fileNames)
            ->map(fn (string $fileName): string => public_path(self::DIRECTORY.'/'.$fileName))
            ->filter(fn (string $path): bool => self::isAllowed($path))
            ->values();
    }

    private static function isAllowed(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        if (filesize($path) > (int) config('media-library.max_file_size', 10 * 1024 * 1024)) {
            return false;
        }

        $dimensions = @getimagesize($path);

        if (! is_array($dimensions)) {
            return false;
        }

        $width = (int) ($dimensions[0] ?? 0);
        $height = (int) ($dimensions[1] ?? 0);

        if ($width < 1 || $height < 1) {
            return false;
        }

        if (max($width, $height) > self::MAX_EDGE) {
            return false;
        }

        return ($width * $height) <= self::MAX_PIXELS;
    }
}
