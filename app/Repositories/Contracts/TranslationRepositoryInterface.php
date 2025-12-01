<?php
namespace App\Repositories\Contracts;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TranslationRepositoryInterface
{
    public function create(array $data): Translation;

    public function update(Translation $translation, array $data): Translation;

    public function delete(Translation $translation): bool;

    public function findById(int $id): ?Translation;

    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function search(array $filters): Collection;

    public function export(?string $locale = null, ?array $tags = null): Collection;
}
