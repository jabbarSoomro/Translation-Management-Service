<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_translation(): void
    {
        $data = [
            'key' => 'welcome.message',
            'locale' => 'en',
            'value' => 'Welcome to our application',
            'tags' => ['web', 'mobile'],
        ];

        $response = $this->postJson('/api/translations', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'key',
                    'locale',
                    'value',
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('translations', [
            'key' => 'welcome.message',
            'locale' => 'en',
            'value' => 'Welcome to our application',
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'web']);
        $this->assertDatabaseHas('tags', ['name' => 'mobile']);
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Original value',
        ]);

        $data = [
            'value' => 'Updated value',
            'tags' => ['web'],
        ];

        $response = $this->putJson("/api/translations/{$translation->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $translation->id,
                    'value' => 'Updated value',
                ],
            ]);

        $this->assertDatabaseHas('translations', [
            'id' => $translation->id,
            'value' => 'Updated value',
        ]);
    }

    public function test_can_get_single_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->getJson("/api/translations/{$translation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $translation->id,
                    'key' => $translation->key,
                    'locale' => $translation->locale,
                    'value' => $translation->value,
                ],
            ]);
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->deleteJson("/api/translations/{$translation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Translation deleted successfully',
            ]);

        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_list_translations(): void
    {
        Translation::factory()->count(15)->create();

        $response = $this->getJson('/api/translations?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'key',
                        'locale',
                        'value',
                        'tags',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_can_search_translations_by_key(): void
    {
        Translation::factory()->create([
            'key' => 'auth.login',
            'locale' => 'en',
            'value' => 'Login',
        ]);

        Translation::factory()->create([
            'key' => 'button.submit',
            'locale' => 'en',
            'value' => 'Submit',
        ]);

        $response = $this->getJson('/api/translations/search?key=auth');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['key' => 'auth.login']);
    }

    public function test_can_search_translations_by_locale(): void
    {
        Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'English value',
        ]);

        Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'fr',
            'value' => 'French value',
        ]);

        $response = $this->getJson('/api/translations/search?locale=fr');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['locale' => 'fr']);
    }

    public function test_can_search_translations_by_content(): void
    {
        Translation::factory()->create([
            'key' => 'test.key1',
            'locale' => 'en',
            'value' => 'This is a test message',
        ]);

        Translation::factory()->create([
            'key' => 'test.key2',
            'locale' => 'en',
            'value' => 'Another message',
        ]);

        $response = $this->getJson('/api/translations/search?content=test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['value' => 'This is a test message']);
    }

    public function test_can_search_translations_by_tags(): void
    {
        $webTag = Tag::factory()->create(['name' => 'web']);
        $mobileTag = Tag::factory()->create(['name' => 'mobile']);

        $translation1 = Translation::factory()->create();
        $translation1->tags()->attach($webTag);

        $translation2 = Translation::factory()->create();
        $translation2->tags()->attach($mobileTag);

        $response = $this->getJson('/api/translations/search?tags=web');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $translation1->id]);
    }

    public function test_can_export_translations_for_locale(): void
    {
        Translation::factory()->create([
            'key' => 'welcome.message',
            'locale' => 'en',
            'value' => 'Welcome',
        ]);

        Translation::factory()->create([
            'key' => 'goodbye.message',
            'locale' => 'en',
            'value' => 'Goodbye',
        ]);

        Translation::factory()->create([
            'key' => 'welcome.message',
            'locale' => 'fr',
            'value' => 'Bienvenue',
        ]);

        $response = $this->getJson('/api/translations/export?locale=en');

        $response->assertStatus(200)
            ->assertJson([
                'locale' => 'en',
                'translations' => [
                    'welcome.message' => 'Welcome',
                    'goodbye.message' => 'Goodbye',
                ],
            ]);
    }

    public function test_can_export_translations_by_tags(): void
    {
        $webTag = Tag::factory()->create(['name' => 'web']);

        $translation = Translation::factory()->create([
            'key' => 'button.submit',
            'locale' => 'en',
            'value' => 'Submit',
        ]);
        $translation->tags()->attach($webTag);

        Translation::factory()->create([
            'key' => 'button.cancel',
            'locale' => 'en',
            'value' => 'Cancel',
        ]);

        $response = $this->getJson('/api/translations/export?locale=en&tags=web');

        $response->assertStatus(200)
            ->assertJson([
                'locale' => 'en',
                'translations' => [
                    'button.submit' => 'Submit',
                ],
            ]);
    }

    public function test_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/translations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'locale', 'value']);
    }

    public function test_cannot_create_duplicate_key_locale_combination(): void
    {
        Translation::factory()->create([
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Test value',
        ]);

        $response = $this->postJson('/api/translations', [
            'key' => 'test.key',
            'locale' => 'en',
            'value' => 'Another value',
        ]);

        $response->assertStatus(500);
    }
}
