<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');

        $this->seedPerformanceData();
    }

    private function seedPerformanceData(): void
    {
        $tags = ['web', 'mobile', 'desktop'];
        foreach ($tags as $tagName) {
            Tag::factory()->create(['name' => $tagName]);
        }

        $translations = [];
        $timestamp = now();

        for ($i = 0; $i < 1000; $i++) {
            $translations[] = [
                'key' => "perf.test.key_{$i}",
                'locale' => ['en', 'fr', 'es'][array_rand(['en', 'fr', 'es'])],
                'value' => "Performance test value {$i}",
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('translations')->insert($translations);

        $insertedIds = DB::table('translations')
            ->where('created_at', $timestamp)
            ->pluck('id')
            ->toArray();

        $tagIds = Tag::pluck('id')->toArray();
        $pivotData = [];

        foreach ($insertedIds as $translationId) {
            $tagId = $tagIds[array_rand($tagIds)];
            $pivotData[] = [
                'translation_id' => $translationId,
                'tag_id' => $tagId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('translation_tag')->insert($pivotData);
    }

    public function test_list_endpoint_performance_under_200ms(): void
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/translations?per_page=10');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(200, $duration, "List endpoint took {$duration}ms, expected < 200ms");
    }

    public function test_search_endpoint_performance_under_200ms(): void
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/translations/search?locale=en&key=perf');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(200, $duration, "Search endpoint took {$duration}ms, expected < 200ms");
    }

    public function test_export_endpoint_performance_under_500ms(): void
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/translations/export?locale=en');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(500, $duration, "Export endpoint took {$duration}ms, expected < 500ms");
    }

    public function test_create_endpoint_performance_under_200ms(): void
    {
        $data = [
            'key' => 'perf.new.key',
            'locale' => 'en',
            'value' => 'New performance test value',
            'tags' => ['web'],
        ];

        $startTime = microtime(true);

        $response = $this->postJson('/api/translations', $data);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(201);

        $this->assertLessThan(200, $duration, "Create endpoint took {$duration}ms, expected < 200ms");
    }

    public function test_update_endpoint_performance_under_200ms(): void
    {
        $translation = Translation::first();

        $data = [
            'value' => 'Updated performance test value',
        ];

        $startTime = microtime(true);

        $response = $this->putJson("/api/translations/{$translation->id}", $data);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(200, $duration, "Update endpoint took {$duration}ms, expected < 200ms");
    }

    public function test_show_endpoint_performance_under_200ms(): void
    {
        $translation = Translation::first();

        $startTime = microtime(true);

        $response = $this->getJson("/api/translations/{$translation->id}");

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(200, $duration, "Show endpoint took {$duration}ms, expected < 200ms");
    }

    public function test_delete_endpoint_performance_under_200ms(): void
    {
        $translation = Translation::first();

        $startTime = microtime(true);

        $response = $this->deleteJson("/api/translations/{$translation->id}");

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(200, $duration, "Delete endpoint took {$duration}ms, expected < 200ms");
    }

    public function test_search_by_tags_performance_under_200ms(): void
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/translations/search?tags=web');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(200, $duration, "Search by tags took {$duration}ms, expected < 200ms");
    }

    public function test_export_with_tags_performance_under_500ms(): void
    {
        $startTime = microtime(true);

        $response = $this->getJson('/api/translations/export?locale=en&tags=web');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        $this->assertLessThan(500, $duration, "Export with tags took {$duration}ms, expected < 500ms");
    }
}
