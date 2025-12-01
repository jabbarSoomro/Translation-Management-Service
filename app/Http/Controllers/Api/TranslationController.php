<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TranslationController extends Controller
{
    public function __construct(
        private TranslationService $translationService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->query('per_page', 10), 100);

        $translations = $this->translationService->listTranslations($perPage);

        return TranslationResource::collection($translations);
    }

    public function store(StoreTranslationRequest $request): TranslationResource
    {
        $translation = $this->translationService->createTranslation(
            $request->validated()
        );

        return new TranslationResource($translation);
    }

    public function show(Translation $translation): TranslationResource
    {
        $translation->load('tags');

        return new TranslationResource($translation);
    }

    public function update(
        UpdateTranslationRequest $request,
        Translation $translation
    ): TranslationResource {
        $updated = $this->translationService->updateTranslation(
            $translation,
            $request->validated()
        );

        return new TranslationResource($updated);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $this->translationService->deleteTranslation($translation);

        return response()->json([
            'message' => 'Translation deleted successfully',
        ]);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $filters = [
            'key' => $request->query('key'),
            'locale' => $request->query('locale'),
            'content' => $request->query('content'),
            'tags' => $request->query('tags')
                ? explode(',', $request->query('tags'))
                : null,
        ];

        $filters = array_filter($filters, fn ($value) => ! is_null($value));

        $translations = $this->translationService->searchTranslations($filters);

        return TranslationResource::collection($translations);
    }

    public function export(Request $request): JsonResponse
    {
        $locale = $request->query('locale');
        $tags = $request->query('tags')
            ? explode(',', $request->query('tags'))
            : null;

        $data = $this->translationService->exportTranslations($locale, $tags);

        return response()->json($data);
    }
}
