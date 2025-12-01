<?php

namespace App\Services;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    public function __construct(
        private TranslationRepositoryInterface $translationRepository
    ) {
    }

    public function createTranslation(array $data): Translation
    {
        $translation = $this->translationRepository->create($data);

        $this->clearCache($data['locale'] ?? null, $data['tags'] ?? []);

        return $translation;
    }

    public function updateTranslation(Translation $translation, array $data): Translation
    {
        $oldLocale = $translation->locale;
        $oldTags = $translation->tags->pluck('name')->toArray();

        $updated = $this->translationRepository->update($translation, $data);

        $this->clearCache($oldLocale, $oldTags);
        $this->clearCache($data['locale'] ?? $updated->locale, $data['tags'] ?? []);

        return $updated;
    }

    public function deleteTranslation(Translation $translation): bool
    {
        $locale = $translation->locale;
        $tags = $translation->tags->pluck('name')->toArray();

        $result = $this->translationRepository->delete($translation);

        if ($result) {
            $this->clearCache($locale, $tags);
        }

        return $result;
    }

    public function getTranslation(int $id): ?Translation
    {
        return $this->translationRepository->findById($id);
    }

    public function listTranslations(int $perPage = 10): LengthAwarePaginator
    {
        return $this->translationRepository->paginate($perPage);
    }

    public function searchTranslations(array $filters): Collection
    {
        return $this->translationRepository->search($filters);
    }

    public function exportTranslations(?string $locale = null, ?array $tags = null): array
    {
        $cacheKey = $this->generateCacheKey($locale, $tags);

        return Cache::remember($cacheKey, 3600, function () use ($locale, $tags) {
            $translations = $this->translationRepository->export($locale, $tags);

            $grouped = $translations->groupBy('locale');

            $result = [];
            foreach ($grouped as $loc => $items) {
                $result[$loc] = $items->pluck('value', 'key')->toArray();
            }

            if ($locale && isset($result[$locale])) {
                return [
                    'locale' => $locale,
                    'translations' => $result[$locale],
                ];
            }

            return [
                'locales' => $result,
            ];
        });
    }

    private function generateCacheKey(?string $locale, ?array $tags): string
    {
        $key = 'translations_export';

        if ($locale) {
            $key .= '_locale_' . $locale;
        }

        if ($tags && count($tags) > 0) {
            sort($tags);
            $key .= '_tags_' . implode('_', $tags);
        }

        return $key;
    }

    private function clearCache(?string $locale, array $tags): void
    {
        $patterns = [
            'translations_export*',
        ];

        if ($locale) {
            $patterns[] = "translations_export_locale_{$locale}*";
        }

        foreach ($patterns as $pattern) {
            $keys = Cache::get('cache_keys', []);
            foreach ($keys as $key) {
                if (fnmatch($pattern, $key)) {
                    Cache::forget($key);
                }
            }
        }

        Cache::forget($this->generateCacheKey(null, []));
        Cache::forget($this->generateCacheKey($locale, []));
        Cache::forget($this->generateCacheKey($locale, $tags));
    }
}
