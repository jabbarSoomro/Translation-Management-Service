<?php
namespace Tests\Unit;

use App\Models\Translation;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = $this->app->make(TranslationRepositoryInterface::class);
        $this->service = new TranslationService($repository);
    }

    public function test_can_create_translation_through_service(): void
    {
        $data = [
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Test value',
            'tags' => ['web'],
        ];

        $translation = $this->service->createTranslation($data);

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals('test.key', $translation->key);
        $this->assertEquals('en', $translation->locale);
        $this->assertEquals('Test value', $translation->value);
        $this->assertCount(1, $translation->tags);
    }

    public function test_can_update_translation_through_service(): void
    {
        $translation = Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Original',
        ]);

        $updated = $this->service->updateTranslation($translation, [
            'value' => 'Updated',
        ]);

        $this->assertEquals('Updated', $updated->value);
    }

    public function test_can_delete_translation_through_service(): void
    {
        $translation = Translation::factory()->create();

        $result = $this->service->deleteTranslation($translation);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_get_translation_through_service(): void
    {
        $translation = Translation::factory()->create();

        $found = $this->service->getTranslation($translation->id);

        $this->assertInstanceOf(Translation::class, $found);
        $this->assertEquals($translation->id, $found->id);
    }

    public function test_export_translations_caches_result(): void
    {
        Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Test value',
        ]);

        Cache::flush();

        $result1 = $this->service->exportTranslations('en');

        $this->assertArrayHasKey('locale', $result1);
        $this->assertArrayHasKey('translations', $result1);

        $cacheKey = 'translations_export_locale_en';
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_creating_translation_clears_cache(): void
    {
        $cacheKey = 'translations_export_locale_en';
        Cache::put($cacheKey, ['test' => 'data'], 3600);

        $this->service->createTranslation([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Test value',
        ]);

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_search_translations_returns_collection(): void
    {
        Translation::factory()->count(5)->create(['locale' => 'en']);
        Translation::factory()->count(3)->create(['locale' => 'fr']);

        $results = $this->service->searchTranslations(['locale' => 'en']);

        $this->assertCount(5, $results);
        $this->assertTrue($results->every(fn($t) => $t->locale === 'en'));
    }
}
