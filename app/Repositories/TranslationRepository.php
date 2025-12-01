<?php

namespace App\Repositories;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function create(array $data): Translation
    {
        return DB::transaction(function () use ($data) {
            $translation = Translation::create([
                'key' => $data['key'],
                'locale' => $data['locale'],
                'value' => $data['value'],
            ]);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $translation->syncTags($data['tags']);
            }

            return $translation->load('tags');
        });
    }

    public function update(Translation $translation, array $data): Translation
    {
        return DB::transaction(function () use ($translation, $data) {
            if (isset($data['value'])) {
                $translation->value = $data['value'];
            }

            if (isset($data['key'])) {
                $translation->key = $data['key'];
            }

            if (isset($data['locale'])) {
                $translation->locale = $data['locale'];
            }

            $translation->save();

            if (isset($data['tags']) && is_array($data['tags'])) {
                $translation->syncTags($data['tags']);
            }

            return $translation->load('tags');
        });
    }

    public function delete(Translation $translation): bool
    {
        return $translation->delete();
    }

    public function findById(int $id): ?Translation
    {
        return Translation::with('tags')->find($id);
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Translation::with('tags')
            ->select(['id', 'key', 'locale', 'value', 'created_at', 'updated_at'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function search(array $filters): Collection
    {
        $query = Translation::query()
            ->select(['translations.id', 'translations.key', 'translations.locale', 'translations.value', 'translations.created_at', 'translations.updated_at']);

        if (isset($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }

        if (isset($filters['locale'])) {
            $query->where('locale', $filters['locale']);
        }

        if (isset($filters['content'])) {
            $query->where('value', 'like', '%' . $filters['content'] . '%');
        }

        if (isset($filters['tags']) && is_array($filters['tags']) && count($filters['tags']) > 0) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.name', $filters['tags']);
            }, '=', count($filters['tags']));
        }

        return $query->with('tags')
            ->orderBy('key')
            ->limit(1000)
            ->get();
    }

    public function export(?string $locale = null, ?array $tags = null): Collection
    {
        $query = Translation::query()
            ->select(['key', 'locale', 'value']);

        if ($locale) {
            $query->where('locale', $locale);
        }

        if ($tags && is_array($tags) && count($tags) > 0) {
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.name', $tags);
            });
        }

        return $query->orderBy('key')->get();
    }
}
